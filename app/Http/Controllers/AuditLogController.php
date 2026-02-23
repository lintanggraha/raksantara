<?php

namespace App\Http\Controllers;

use App\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogController extends Controller
{
    private const EVENTS = [
        'auth.login.succeeded',
        'auth.logout',
        'risk.created',
        'risk.updated',
        'risk.deleted',
        'risk.import.completed',
        'risk.import.failed',
        'report.export.generated',
        'report.export.failed',
    ];

    public function index(Request $request)
    {
        $request->validate([
            'event' => 'nullable|string|max:80',
        ]);

        $event = trim((string) $request->input('event', ''));
        if ($event !== '' && !in_array($event, self::EVENTS, true)) {
            $event = '';
        }

        $query = AuditLog::with('actor')
            ->whereIn('event', self::EVENTS)
            ->orderByDesc('created_at');

        if ($event !== '') {
            $query->where('event', $event);
        }

        $logs = $query->paginate(40)->appends([
            'event' => $event,
        ]);

        $logs->setCollection(
            $logs->getCollection()->map(function (AuditLog $log) {
                return [
                    'time' => optional($log->created_at)->format('d-m-Y H:i:s') ?? '-',
                    'actor' => optional($log->actor)->name ?? 'System',
                    'activity' => $this->activityLabel((string) $log->event),
                    'details' => $this->activityDetails($log),
                ];
            })
        );

        $events = collect(self::EVENTS)->mapWithKeys(function (string $name) {
            return [$name => $this->activityLabel($name)];
        });

        return view('audit.index', compact('logs', 'events', 'event'));
    }

    private function activityLabel(string $event): string
    {
        $map = [
            'auth.login.succeeded' => 'Login',
            'auth.logout' => 'Logout',
            'risk.created' => 'Add Risk',
            'risk.updated' => 'Edit Risk',
            'risk.deleted' => 'Delete Risk',
            'risk.import.completed' => 'Import Risk',
            'risk.import.failed' => 'Import Risk (Failed)',
            'report.export.generated' => 'Export Report',
            'report.export.failed' => 'Export Report (Failed)',
        ];

        return $map[$event] ?? $event;
    }

    private function activityDetails(AuditLog $log): string
    {
        switch ((string) $log->event) {
            case 'auth.login.succeeded':
                return 'Login berhasil' . $this->ipSuffix($log);
            case 'auth.logout':
                return 'Logout' . $this->ipSuffix($log);
            case 'risk.created':
            case 'risk.updated':
            case 'risk.deleted':
                return $this->riskSummary($log);
            case 'risk.import.completed':
            case 'risk.import.failed':
                return $this->importSummary($log);
            case 'report.export.generated':
            case 'report.export.failed':
                return $this->exportSummary($log);
            default:
                return '-';
        }
    }

    private function riskSummary(AuditLog $log): string
    {
        $after = is_array($log->after_state) ? $log->after_state : [];
        $before = is_array($log->before_state) ? $log->before_state : [];
        $state = !empty($after) ? $after : $before;

        $riskId = trim((string) ($state['risk_id'] ?? ''));
        $title = trim((string) ($state['risk_title'] ?? ''));
        $status = trim((string) ($state['status'] ?? ''));

        $parts = [];
        if ($riskId !== '') {
            $parts[] = 'Risk: ' . $riskId;
        }
        if ($title !== '') {
            $parts[] = 'Title: ' . Str::limit($title, 80, '...');
        }
        if ($status !== '') {
            $parts[] = 'Status: ' . $status;
        }

        if (empty($parts)) {
            $parts[] = class_basename((string) $log->auditable_type) . '#' . (string) $log->auditable_id;
        }

        return implode(' | ', $parts);
    }

    private function importSummary(AuditLog $log): string
    {
        $after = is_array($log->after_state) ? $log->after_state : [];

        $fileName = (string) ($after['file_name'] ?? '-');
        $year = $after['year'] ?? null;
        $dryRun = (bool) ($after['dry_run'] ?? false);

        $parts = ['File: ' . $fileName];
        if (!is_null($year) && $year !== '') {
            $parts[] = 'Year: ' . $year;
        }
        if ($dryRun) {
            $parts[] = 'Mode: dry-run';
        }

        if ((string) $log->event === 'risk.import.failed') {
            $reason = (string) ($after['reason'] ?? 'failed');
            $parts[] = 'Status: gagal (' . $reason . ')';
        } else {
            $parts[] = 'Status: sukses';
        }

        return implode(' | ', $parts);
    }

    private function exportSummary(AuditLog $log): string
    {
        $after = is_array($log->after_state) ? $log->after_state : [];

        $scope = (string) ($after['scope'] ?? '-');
        $format = strtoupper((string) ($after['format'] ?? '-'));
        $year = $after['year'] ?? null;

        $parts = [
            'Scope: ' . $scope,
            'Format: ' . $format,
        ];

        if (!is_null($year) && $year !== '') {
            $parts[] = 'Year: ' . $year;
        }

        $parts[] = (string) $log->event === 'report.export.failed'
            ? 'Status: gagal'
            : 'Status: sukses';

        return implode(' | ', $parts);
    }

    private function ipSuffix(AuditLog $log): string
    {
        $ip = trim((string) $log->ip_address);
        if ($ip === '') {
            return '';
        }

        return ' | IP: ' . $ip;
    }
}
