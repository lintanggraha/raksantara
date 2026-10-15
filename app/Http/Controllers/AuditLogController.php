<?php

namespace App\Http\Controllers;

use App\AuditLog;
use App\Support\AuditLogger;
use App\Support\AuditTrail;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Audit trail for superadmins: every recorded event, filterable, with the
 * exact field changes and the people involved in approvals.
 */
class AuditLogController extends Controller
{
    private const PER_PAGE = 50;
    private const EXPORT_LIMIT = 20000;

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $logs = $this->query($filters)->paginate(self::PER_PAGE)->withQueryString();
        $rows = AuditTrail::present($logs->getCollection());

        $events = AuditLog::query()->distinct()->orderBy('event')->pluck('event');
        $modules = $events->map(fn ($event) => AuditTrail::moduleOf($event))->unique()
            ->mapWithKeys(fn ($module) => [$module => AuditTrail::moduleLabel($module)])->sort();
        $actors = User::query()
            ->whereIn('id', AuditLog::query()->whereNotNull('actor_id')->distinct()->select('actor_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('audit.index', [
            'logs' => $logs,
            'rows' => $rows,
            'filters' => $filters,
            'events' => $events->mapWithKeys(fn ($event) => [$event => AuditTrail::eventLabel($event)]),
            'modules' => $modules,
            'actors' => $actors,
            'timezoneLabel' => AuditTrail::DISPLAY_TIMEZONE_LABEL,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        $total = $this->query($filters)->count();

        AuditLogger::logModelEvent('audit.exported', new AuditLog(), null, array_filter($filters) + [
            'rows' => min($total, self::EXPORT_LIMIT),
        ]);

        $fileName = 'audit-log-' . now(AuditTrail::DISPLAY_TIMEZONE)->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($filters) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // Excel reads UTF-8 with a BOM
            fputcsv($out, ['Hari', 'Tanggal & jam (' . AuditTrail::DISPLAY_TIMEZONE_LABEL . ')', 'Pengguna', 'Username / divisi', 'Modul', 'Aktivitas', 'Objek', 'Persetujuan', 'Perubahan', 'IP', 'Browser']);

            // chunk() would drop a limit(), so cap the lazy stream instead.
            $this->query($filters)->lazy(500)->take(self::EXPORT_LIMIT)->chunk(500)->each(function ($chunk) use ($out) {
                foreach (AuditTrail::present(collect($chunk->all())->values()) as $row) {
                    $changes = collect($row['changes'])->map(fn ($change) => $change['field'] . ': '
                        . ($change['before'] ?? '-') . ' → ' . ($change['after'] ?? '-'))->implode("\n");
                    fputcsv($out, [
                        $row['day'], $row['timestamp'], $row['actor'], $row['actor_detail'], $row['module'], $row['activity'],
                        $row['object'], $row['approval'], $changes, $row['ip'], $row['user_agent'],
                    ]);
                }
            });

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'actor' => 'nullable|integer',
            'module' => 'nullable|string|max:60',
            'event' => 'nullable|string|max:80',
            'q' => 'nullable|string|max:120',
            'ip' => 'nullable|string|max:45',
        ]);

        return [
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
            'actor' => isset($validated['actor']) ? (int) $validated['actor'] : null,
            'module' => $validated['module'] ?? null,
            'event' => $validated['event'] ?? null,
            'q' => isset($validated['q']) ? trim($validated['q']) : null,
            'ip' => $validated['ip'] ?? null,
        ];
    }

    private function query(array $filters): Builder
    {
        $query = AuditLog::query()->with('actor:id,name,username,division')->orderByDesc('created_at')->orderByDesc('id');

        // Dates are picked in local time; the table stores UTC.
        if ($filters['from']) {
            $query->where('created_at', '>=', CarbonImmutable::parse($filters['from'], AuditTrail::DISPLAY_TIMEZONE)->startOfDay()->utc());
        }
        if ($filters['to']) {
            $query->where('created_at', '<=', CarbonImmutable::parse($filters['to'], AuditTrail::DISPLAY_TIMEZONE)->endOfDay()->utc());
        }
        if ($filters['actor']) {
            $query->where('actor_id', $filters['actor']);
        }
        if ($filters['module']) {
            $query->where(fn ($q) => $q->where('event', $filters['module'])->orWhere('event', 'like', $filters['module'] . '.%'));
        }
        if ($filters['event']) {
            $query->where('event', $filters['event']);
        }
        if ($filters['ip']) {
            $query->where('ip_address', $filters['ip']);
        }
        if ($filters['q']) {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['q']) . '%';
            $cast = DB::connection()->getDriverName() === 'mysql' ? 'CHAR' : 'TEXT';
            $query->where(function ($q) use ($term, $cast, $filters) {
                $q->where('event', 'like', $term)
                    ->orWhereRaw("CAST(before_state AS {$cast}) LIKE ?", [$term])
                    ->orWhereRaw("CAST(after_state AS {$cast}) LIKE ?", [$term])
                    ->orWhereHas('actor', fn ($a) => $a->where('name', 'like', $term)->orWhere('username', 'like', $term));
                if (ctype_digit($filters['q'])) {
                    $q->orWhere('auditable_id', (int) $filters['q']);
                }
            });
        }

        return $query;
    }
}
