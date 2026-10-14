<?php

namespace App\Http\Controllers;

use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class RiskImportController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        abort_if($user->isSuperadmin(), 403, 'Superadmin is read-only for risk dashboard.');
        abort_unless($user->isComplianceDivision(), 403, 'Risk import is only available for Compliance division.');

        $validated = $request->validate([
            'file' => 'required|file|max:20480|mimes:xls,xlsx',
            'year' => 'nullable|integer|min:2000|max:2100',
            'dry_run' => 'nullable|boolean',
        ]);

        $dryRun = (bool) ($validated['dry_run'] ?? false);
        $importAuditPayload = [
            'file_name' => $request->file('file')->getClientOriginalName(),
            'year' => $validated['year'] ?? null,
            'dry_run' => $dryRun,
        ];

        $storedPath = $request->file('file')->store('imports/risk-register', 'local');
        $absolutePath = storage_path('app/' . $storedPath);

        try {
            $commandParams = [
                'file' => $absolutePath,
            ];

            if ((int) $user->id > 0) {
                $commandParams['--actor-id'] = (string) $user->id;
            }

            if (!empty($validated['year'])) {
                $commandParams['--year'] = (string) $validated['year'];
            }

            if ($dryRun) {
                $commandParams['--dry-run'] = true;
            }

            $exitCode = Artisan::call('risk:import-register', $commandParams);
            $commandOutput = trim((string) Artisan::output());
            if (strlen($commandOutput) > 5000) {
                $commandOutput = substr($commandOutput, 0, 5000) . PHP_EOL . '... output truncated ...';
            }

            if ($exitCode !== 0) {
                AuditLogger::logModelEvent('risk.import.failed', $user, null, array_merge($importAuditPayload, [
                    'reason' => 'command_exit_non_zero',
                    'exit_code' => $exitCode,
                ]));

                return redirect()
                    ->route('risks.index')
                    ->with('error', 'Import gagal. Cek output import untuk detail.')
                    ->with('import_output', $commandOutput);
            }

            $successMessage = $dryRun
                ? 'Import dry-run selesai. Data tidak disimpan.'
                : 'Import risk register selesai.';

            AuditLogger::logModelEvent('risk.import.completed', $user, null, array_merge($importAuditPayload, [
                'exit_code' => $exitCode,
            ]));

            return redirect()
                ->route('risks.index')
                ->with('success', $successMessage)
                ->with('import_output', $commandOutput);
        } catch (\Throwable $e) {
            $incidentId = (string) \Illuminate\Support\Str::uuid();
            AuditLogger::logModelEvent('risk.import.failed', $user, null, array_merge($importAuditPayload, [
                'reason' => 'exception',
                'incident_id' => $incidentId,
                'exception_class' => get_class($e),
                'exception_file' => basename($e->getFile()),
                'exception_line' => $e->getLine(),
            ]));

            return redirect()
                ->route('risks.index')
                ->with('error', 'Import gagal. Referensi insiden: ' . $incidentId . '.');
        } finally {
            if (!empty($storedPath) && Storage::disk('local')->exists($storedPath)) {
                Storage::disk('local')->delete($storedPath);
            }
        }
    }
}
