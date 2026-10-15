<?php

namespace App\Http\Controllers;

use App\RiskCategory;
use App\RiskCauseCategory;
use App\ScheduledReport;
use App\ScheduledReportRun;
use App\Services\ReportDataService;
use App\Services\ScheduledReportDispatchService;
use App\Support\RiskOwnerAccess;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScheduledReportController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureSchedulerAllowed();

        // Compliance owns the application and manages every user's schedules.
        $schedules = ScheduledReport::query()
            ->with('user:id,name')
            ->when(!auth()->user()->isComplianceDivision(), fn ($query) => $query->where('user_id', auth()->id()))
            ->orderByDesc('created_at')
            ->get();

        $scheduleIds = $schedules->pluck('id')->filter()->values();
        $runHistory = $scheduleIds->isEmpty()
            ? collect()
            : ScheduledReportRun::query()
                ->whereIn('scheduled_report_id', $scheduleIds)
                ->orderByDesc('ran_at')
                ->limit(500)
                ->get()
                ->groupBy('scheduled_report_id');

        $editingSchedule = null;
        $editId = $request->query('edit');
        if (is_numeric($editId)) {
            $editingSchedule = $schedules->firstWhere('id', (int) $editId);
        }

        $categories = RiskCategory::query()->orderBy('name')->get(['id', 'name']);
        $causeCategories = RiskCauseCategory::query()->orderBy('name')->get(['id', 'name']);

        $timezones = DateTimeZone::listIdentifiers();
        $timezones = array_values(array_unique(array_merge([
            'Asia/Jakarta',
            'Asia/Singapore',
            'UTC',
        ], $timezones)));

        $riskOwners = optional(auth()->user())->allowedRiskOwners() ?: [];
        usort($riskOwners, function ($left, $right) {
            return strnatcasecmp(
                RiskOwnerAccess::displayLabel((string) $left, (string) $left),
                RiskOwnerAccess::displayLabel((string) $right, (string) $right)
            );
        });

        return view('reports.scheduled.index', compact(
            'schedules',
            'runHistory',
            'editingSchedule',
            'categories',
            'causeCategories',
            'timezones',
            'riskOwners'
        ));
    }

    public function store(Request $request, ScheduledReportDispatchService $dispatchService): RedirectResponse
    {
        $this->ensureSchedulerAllowed();

        try {
            $validated = $this->validateScheduleInput($request);
            $this->persistSchedule($validated, $dispatchService);

            return redirect()->route('reports.scheduled.index')->with('success', 'Scheduled report berhasil dibuat.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $incidentId = (string) \Illuminate\Support\Str::uuid();
            \Log::error('Failed to store scheduled report', [
                'incident_id' => $incidentId,
                'user_id' => auth()->id(),
                'exception_class' => get_class($e),
                'exception_file' => basename($e->getFile()),
                'exception_line' => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->withErrors(['store' => 'Gagal menyimpan scheduled report. Referensi insiden: ' . $incidentId . '.'])
                ->withInput();
        }
    }

    public function update(
        Request $request,
        ScheduledReport $scheduledReport,
        ScheduledReportDispatchService $dispatchService
    ): RedirectResponse {
        $this->ensureSchedulerAllowed();
        $this->authorizeSchedule($scheduledReport);

        try {
            $validated = $this->validateScheduleInput($request);
            $this->persistSchedule($validated, $dispatchService, $scheduledReport);

            return redirect()->route('reports.scheduled.index')->with('success', 'Scheduled report berhasil diperbarui.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $incidentId = (string) \Illuminate\Support\Str::uuid();
            \Log::error('Failed to update scheduled report', [
                'incident_id' => $incidentId,
                'id' => $scheduledReport->id,
                'user_id' => auth()->id(),
                'exception_class' => get_class($e),
                'exception_file' => basename($e->getFile()),
                'exception_line' => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->withErrors(['update' => 'Gagal memperbarui scheduled report. Referensi insiden: ' . $incidentId . '.'])
                ->withInput();
        }
    }

    public function toggle(ScheduledReport $scheduledReport, ScheduledReportDispatchService $dispatchService): RedirectResponse
    {
        $this->ensureSchedulerAllowed();
        $this->authorizeSchedule($scheduledReport);

        $scheduledReport->is_active = !$scheduledReport->is_active;
        if ($scheduledReport->is_active) {
            $scheduledReport->next_run_at = $dispatchService->computeNextRunAt(
                (string) $scheduledReport->frequency,
                (string) $scheduledReport->send_time,
                (string) $scheduledReport->timezone,
                is_null($scheduledReport->day_of_week) ? null : (int) $scheduledReport->day_of_week,
                is_null($scheduledReport->day_of_month) ? null : (int) $scheduledReport->day_of_month
            );
        } else {
            $scheduledReport->next_run_at = null;
        }

        $scheduledReport->save();

        return redirect()->route('reports.scheduled.index')->with('success', 'Status schedule berhasil diperbarui.');
    }

    public function runNow(
        ScheduledReport $scheduledReport,
        ScheduledReportDispatchService $dispatchService,
        ReportDataService $reportDataService
    ): RedirectResponse {
        $this->ensureSchedulerAllowed();
        $this->authorizeSchedule($scheduledReport);

        $result = $dispatchService->dispatchSchedule($scheduledReport->loadMissing('user'), $reportDataService, 'manual');
        \App\Support\AuditLogger::logModelEvent('scheduled_report.run_manually', $scheduledReport, null, [
            'name' => $scheduledReport->name,
            'user_id' => $scheduledReport->user_id,
            'success' => (bool) ($result['success'] ?? false),
            'recipient_count' => (int) ($result['recipient_count'] ?? 0),
            'error' => $result['error'] ?? null,
        ]);
        if ($result['success'] ?? false) {
            return redirect()->route('reports.scheduled.index')->with(
                'success',
                'Scheduled report berhasil dijalankan. Terkirim ke ' . (int) ($result['recipient_count'] ?? 0) . ' penerima.'
            );
        }

        return redirect()
            ->route('reports.scheduled.index')
            ->withErrors([
                'schedule_run' => 'Gagal menjalankan schedule "' . $scheduledReport->name . '": ' . (string) ($result['error'] ?? 'Unknown error'),
            ]);
    }

    public function destroy(ScheduledReport $scheduledReport): RedirectResponse
    {
        $this->ensureSchedulerAllowed();
        $this->authorizeSchedule($scheduledReport);

        $scheduledReport->delete();

        return redirect()->route('reports.scheduled.index')->with('success', 'Scheduled report berhasil dihapus.');
    }

    private function authorizeSchedule(ScheduledReport $scheduledReport): void
    {
        abort_unless(
            (int) $scheduledReport->user_id === (int) auth()->id() || auth()->user()->isComplianceDivision(),
            403
        );
    }

    private function ensureSchedulerAllowed(): void
    {
        abort_if(optional(auth()->user())->isSuperadmin(), 403, 'Superadmin is read-only for risk dashboard.');
    }

    private function validateScheduleInput(Request $request): array
    {
        $allowedRiskOwners = optional(auth()->user())->allowedRiskOwners() ?: [];
        $rawRiskOwner = trim((string) $request->input('risk_owner', ''));
        if ($rawRiskOwner !== '') {
            $request->merge([
                'risk_owner' => RiskOwnerAccess::canonicalOption($rawRiskOwner) ?: $rawRiskOwner,
            ]);
        }

        return $request->validate([
            'name' => 'required|string|max:120',
            'scope' => 'required|in:monitoring,board,risks,heatmap,recap,overall',
            'format' => 'required|in:pdf,xlsx,pptx',
            'frequency' => 'required|in:daily,weekly,monthly',
            'send_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'day_of_week' => 'nullable|integer|min:0|max:6|required_if:frequency,weekly',
            'day_of_month' => 'nullable|integer|min:1|max:28|required_if:frequency,monthly',
            'timezone' => 'required|timezone',
            'recipients' => 'nullable|string|max:1000',
            'year' => 'nullable|integer|min:2000|max:2100',
            'risk_category_id' => 'nullable|integer|exists:risk_categories,id',
            'risk_cause_category_id' => 'nullable|integer|exists:risk_cause_categories,id',
            'risk_owner' => ['nullable', 'string', 'max:120', Rule::in($allowedRiskOwners)],
        ], [
            'risk_owner.in' => 'Risk owner filter is not allowed for your account.',
        ]);
    }

    private function persistSchedule(
        array $validated,
        ScheduledReportDispatchService $dispatchService,
        ?ScheduledReport $scheduledReport = null
    ): ScheduledReport {
        $recipients = $dispatchService->parseRecipients(
            (string) ($validated['recipients'] ?? ''),
            (string) (optional(auth()->user())->email ?? '')
        );

        if (empty($recipients)) {
            throw ValidationException::withMessages([
                'recipients' => 'Isi minimal satu email penerima yang valid.',
            ]);
        }

        $riskOwner = null;
        if (!empty($validated['risk_owner'])) {
            $riskOwner = RiskOwnerAccess::canonicalOption((string) $validated['risk_owner'])
                ?: trim((string) $validated['risk_owner']);
        }

        $filters = [
            'year' => array_key_exists('year', $validated) ? $validated['year'] : null,
            'risk_category_id' => $validated['risk_category_id'] ?? null,
            'risk_cause_category_id' => $validated['risk_cause_category_id'] ?? null,
            'risk_owner' => $riskOwner,
        ];

        $isActive = is_null($scheduledReport) ? true : (bool) $scheduledReport->is_active;
        $dayOfWeek = $validated['frequency'] === 'weekly' ? (int) ($validated['day_of_week'] ?? 1) : null;
        $dayOfMonth = $validated['frequency'] === 'monthly' ? (int) ($validated['day_of_month'] ?? 1) : null;

        $nextRunAt = $isActive
            ? $dispatchService->computeNextRunAt(
                (string) $validated['frequency'],
                (string) $validated['send_time'],
                (string) $validated['timezone'],
                $dayOfWeek,
                $dayOfMonth
            )
            : null;

        $payload = [
            'name' => (string) $validated['name'],
            'scope' => (string) $validated['scope'],
            'format' => (string) $validated['format'],
            'frequency' => (string) $validated['frequency'],
            'send_time' => (string) $validated['send_time'],
            'day_of_week' => $dayOfWeek,
            'day_of_month' => $dayOfMonth,
            'timezone' => (string) $validated['timezone'],
            'recipients' => $recipients,
            'filters' => $filters,
            'next_run_at' => $nextRunAt,
        ];

        if (is_null($scheduledReport)) {
            $payload['user_id'] = (int) auth()->id();
            $payload['is_active'] = true;

            return ScheduledReport::query()->create($payload);
        }

        $scheduledReport->fill($payload);
        $scheduledReport->save();

        return $scheduledReport;
    }
}
