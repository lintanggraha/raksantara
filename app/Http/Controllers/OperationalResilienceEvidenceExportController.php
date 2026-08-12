<?php

namespace App\Http\Controllers;

use App\AuditLog;
use App\DataPurgeRequest;
use App\DataRetentionSchedule;
use App\Exports\MultiSheetReportExport;
use App\ResilienceBackupRecord;
use App\ResilienceRestoreTest;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OperationalResilienceEvidenceExportController extends Controller
{
    public function download(Request $request)
    {
        $user = $request->user();
        abort_unless(!is_null($user) && $user->isComplianceDivision(), 403, 'Only compliance may export the resilience audit pack.');

        $sections = [
            $this->backupSection(),
            $this->restoreTestSection(),
            $this->retentionSection(),
            $this->purgeSection(),
            $this->auditTrailSection(),
        ];
        $filename = 'operational-resilience-audit-pack-' . now()->format('Ymd-His') . '.xlsx';
        AuditLogger::logModelEvent('resilience.evidence_pack.exported', $user, null, [
            'format' => 'xlsx',
            'section_count' => count($sections),
        ]);

        return Excel::download(new MultiSheetReportExport($sections), $filename);
    }

    private function backupSection(): array
    {
        $rows = ResilienceBackupRecord::query()->latest('completed_at')->get()->map(function (ResilienceBackupRecord $record) {
            return [
                $record->backup_reference,
                $record->asset_scope,
                $record->environment,
                $record->status,
                $record->encryption_status,
                optional($record->started_at)->format('Y-m-d H:i:s'),
                optional($record->completed_at)->format('Y-m-d H:i:s'),
                optional($record->retention_until)->format('Y-m-d H:i:s'),
                $record->checksum,
                $record->storage_location,
                $record->evidence_reference,
            ];
        })->all();

        return [
            'title' => 'Backup Register',
            'headings' => ['Reference', 'Asset Scope', 'Environment', 'Status', 'Encryption', 'Started At', 'Completed At', 'Retention Until', 'Checksum', 'Storage Location', 'Evidence Reference'],
            'rows' => $rows,
        ];
    }

    private function restoreTestSection(): array
    {
        $rows = ResilienceRestoreTest::query()->with('backupRecord:id,backup_reference')->latest('performed_at')->get()->map(function (ResilienceRestoreTest $test) {
            return [
                optional($test->backupRecord)->backup_reference,
                $test->test_type,
                optional($test->performed_at)->format('Y-m-d H:i:s'),
                $test->result,
                $test->recovery_time_minutes,
                $test->recovery_point_minutes,
                $test->findings,
                $test->remediation_plan,
                $test->evidence_reference,
                optional($test->verified_at)->format('Y-m-d H:i:s'),
            ];
        })->all();

        return [
            'title' => 'Restore Tests',
            'headings' => ['Backup Reference', 'Test Type', 'Performed At', 'Result', 'RTO Minutes', 'RPO Minutes', 'Findings', 'Remediation Plan', 'Evidence Reference', 'Verified At'],
            'rows' => $rows,
        ];
    }

    private function retentionSection(): array
    {
        $rows = DataRetentionSchedule::query()->orderBy('record_type')->get()->map(function (DataRetentionSchedule $schedule) {
            return [
                $schedule->record_type,
                $schedule->retention_months,
                $schedule->owner_name,
                $schedule->status,
                optional($schedule->next_review_at)->format('Y-m-d'),
                $schedule->legal_basis,
                $schedule->description,
            ];
        })->all();

        return [
            'title' => 'Retention Schedules',
            'headings' => ['Record Type', 'Retention Months', 'Owner', 'Status', 'Next Review', 'Legal or Policy Basis', 'Description'],
            'rows' => $rows,
        ];
    }

    private function purgeSection(): array
    {
        $rows = DataPurgeRequest::query()->with('schedule:id,record_type')->latest('requested_at')->get()->map(function (DataPurgeRequest $purge) {
            return [
                optional($purge->schedule)->record_type,
                $purge->scope_reference,
                $purge->estimated_record_count,
                optional($purge->requested_at)->format('Y-m-d H:i:s'),
                optional($purge->proposed_purge_at)->format('Y-m-d H:i:s'),
                $purge->legal_hold_checked ? 'Yes' : 'No',
                $purge->status,
                optional($purge->reviewed_at)->format('Y-m-d H:i:s'),
                optional($purge->executed_at)->format('Y-m-d H:i:s'),
                $purge->evidence_reference,
                $purge->review_notes,
            ];
        })->all();

        return [
            'title' => 'Purge Control',
            'headings' => ['Retention Schedule', 'Scope Reference', 'Estimated Records', 'Requested At', 'Proposed Purge At', 'Legal Hold Checked', 'Status', 'Reviewed At', 'Executed At', 'Execution Evidence', 'Review Notes'],
            'rows' => $rows,
        ];
    }

    private function auditTrailSection(): array
    {
        $rows = AuditLog::query()
            ->where(function ($query) {
                $query->where('event', 'like', 'resilience.%')
                    ->orWhere('event', 'like', 'retention.%');
            })
            ->latest()
            ->limit(1000)
            ->get()
            ->map(function (AuditLog $log) {
                return [
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                    $log->event,
                    $log->user_id,
                    $log->auditable_type,
                    $log->auditable_id,
                    json_encode($log->new_values),
                ];
            })->all();

        return [
            'title' => 'Resilience Audit Trail',
            'headings' => ['Timestamp', 'Event', 'User ID', 'Auditable Type', 'Auditable ID', 'Metadata'],
            'rows' => $rows,
        ];
    }
}
