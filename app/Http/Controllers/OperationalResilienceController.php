<?php

namespace App\Http\Controllers;

use App\ResilienceBackupRecord;
use App\ResilienceRestoreTest;
use App\Support\AuditLogger;
use App\Support\InputValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OperationalResilienceController extends Controller
{
    public function index(): View
    {
        $backups = ResilienceBackupRecord::query()
            ->with(['restoreTests.verifier:id,name'])
            ->latest('completed_at')
            ->paginate(25);
        $restoreTests = ResilienceRestoreTest::query()
            ->with('backupRecord:id,backup_reference,asset_scope,environment')
            ->latest('performed_at')
            ->limit(12)
            ->get();

        $summary = [
            'successful_backups' => ResilienceBackupRecord::query()->where('status', 'successful')->count(),
            'failed_backups' => ResilienceBackupRecord::query()->where('status', 'failed')->count(),
            'unverified_restore_tests' => ResilienceRestoreTest::query()->whereNull('verified_at')->count(),
            'failed_restore_tests' => ResilienceRestoreTest::query()->where('result', 'failed')->count(),
        ];

        return view('operational-resilience.index', [
            'backups' => $backups,
            'restoreTests' => $restoreTests,
            'summary' => $summary,
            'canManage' => optional(auth()->user())->isComplianceDivision(),
            'assetScopes' => ResilienceBackupRecord::ASSET_SCOPES,
            'backupStatuses' => ResilienceBackupRecord::STATUSES,
            'encryptionStatuses' => ResilienceBackupRecord::ENCRYPTION_STATUSES,
            'testTypes' => ResilienceRestoreTest::TEST_TYPES,
            'testResults' => ResilienceRestoreTest::RESULTS,
        ]);
    }

    public function storeBackup(Request $request): RedirectResponse
    {
        $this->ensureCanManage();
        $validated = $request->validate([
            'backup_reference' => ['nullable', 'string', 'max:100', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'asset_scope' => ['required', 'in:' . implode(',', ResilienceBackupRecord::ASSET_SCOPES)],
            'environment' => ['required', 'string', 'max:40', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'storage_location' => ['required', 'string', 'max:500', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'encryption_status' => ['required', 'in:' . implode(',', ResilienceBackupRecord::ENCRYPTION_STATUSES)],
            'status' => ['required', 'in:' . implode(',', ResilienceBackupRecord::STATUSES)],
            'started_at' => ['required', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'checksum' => ['nullable', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'retention_until' => ['nullable', 'date', 'after:started_at'],
            'evidence_reference' => ['nullable', 'string', 'max:500', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'notes' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], $this->safeTextMessages());

        $validated['backup_reference'] = $validated['backup_reference'] ?: $this->backupReference($validated['asset_scope']);
        $validated['recorded_by'] = auth()->id();
        $backup = ResilienceBackupRecord::create($validated);
        AuditLogger::logModelEvent('resilience.backup.recorded', $backup, null, $backup->toArray());

        return redirect()->route('operational-resilience.index')->with('success', 'Backup record saved.');
    }

    public function storeRestoreTest(Request $request, ResilienceBackupRecord $backup): RedirectResponse
    {
        $this->ensureCanManage();
        $validated = $request->validate([
            'test_type' => ['required', 'in:' . implode(',', ResilienceRestoreTest::TEST_TYPES)],
            'performed_at' => ['required', 'date'],
            'recovery_time_minutes' => ['nullable', 'integer', 'min:0', 'max:525600'],
            'recovery_point_minutes' => ['nullable', 'integer', 'min:0', 'max:525600'],
            'result' => ['required', 'in:' . implode(',', ResilienceRestoreTest::RESULTS)],
            'findings' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'remediation_plan' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'evidence_reference' => ['nullable', 'string', 'max:500', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], $this->safeTextMessages());
        $validated['resilience_backup_record_id'] = $backup->id;
        $validated['performed_by'] = auth()->id();

        $restoreTest = ResilienceRestoreTest::create($validated);
        AuditLogger::logModelEvent('resilience.restore_test.recorded', $backup, null, [
            'restore_test_id' => $restoreTest->id,
            'result' => $restoreTest->result,
            'test_type' => $restoreTest->test_type,
        ]);

        return redirect()->route('operational-resilience.index')->with('success', 'Restore-test evidence saved.');
    }

    public function verifyRestoreTest(ResilienceRestoreTest $restoreTest): RedirectResponse
    {
        $this->ensureCanManage();
        abort_if(!is_null($restoreTest->verified_at), 422, 'Restore test is already verified.');

        $restoreTest->update(['verified_by' => auth()->id(), 'verified_at' => now()]);
        AuditLogger::logModelEvent('resilience.restore_test.verified', $restoreTest->backupRecord, null, [
            'restore_test_id' => $restoreTest->id,
            'verified_by' => auth()->id(),
        ]);

        return redirect()->route('operational-resilience.index')->with('success', 'Restore test verified.');
    }

    private function backupReference(string $assetScope): string
    {
        return 'BKP-' . strtoupper(Str::substr($assetScope, 0, 4)) . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
    }

    private function ensureCanManage(): void
    {
        abort_unless(optional(auth()->user())->isComplianceDivision(), 403, 'Only compliance may manage operational resilience records.');
    }

    private function safeTextMessages(): array
    {
        return [
            'backup_reference.regex' => InputValidation::safeTextMessage(),
            'environment.regex' => InputValidation::safeTextMessage(),
            'storage_location.regex' => InputValidation::safeTextMessage(),
            'checksum.regex' => InputValidation::safeTextMessage(),
            'evidence_reference.regex' => InputValidation::safeTextMessage(),
            'notes.regex' => InputValidation::safeTextMessage(),
            'findings.regex' => InputValidation::safeTextMessage(),
            'remediation_plan.regex' => InputValidation::safeTextMessage(),
        ];
    }
}
