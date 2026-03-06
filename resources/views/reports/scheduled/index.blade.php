@extends('layouts.tabler')

@section('title', 'Scheduled Reports')

@section('content')
@php
    $isEditing = isset($editingSchedule) && $editingSchedule instanceof \App\ScheduledReport;
    $editingFilters = $isEditing && is_array($editingSchedule->filters) ? $editingSchedule->filters : [];
    $editingRecipients = $isEditing && is_array($editingSchedule->recipients) ? implode(', ', $editingSchedule->recipients) : '';

    $formAction = $isEditing
        ? route('reports.scheduled.update', $editingSchedule->id)
        : route('reports.scheduled.store');

    $defaultName = $isEditing ? $editingSchedule->name : 'Executive Risk Snapshot';
    $defaultScope = $isEditing ? $editingSchedule->scope : 'board';
    $defaultFormat = $isEditing ? $editingSchedule->format : 'pdf';
    $defaultFrequency = $isEditing ? $editingSchedule->frequency : 'weekly';
    $defaultSendTime = $isEditing ? $editingSchedule->send_time : '08:00';
    $defaultTimezone = $isEditing ? $editingSchedule->timezone : 'Asia/Jakarta';
    $defaultDayOfWeek = $isEditing ? $editingSchedule->day_of_week : 1;
    $defaultDayOfMonth = $isEditing ? $editingSchedule->day_of_month : 1;
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h1 class="mb-0">Automated Scheduled Reports</h1>
    <a href="{{ route('risks.monitoring') }}" class="btn btn-outline-secondary btn-sm with-icon">
        <i class="fas fa-arrow-left"></i> Back to Monitoring
    </a>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>{{ $isEditing ? 'Edit Schedule' : 'Create Schedule' }}</strong>
        @if($isEditing)
            <a href="{{ route('reports.scheduled.index') }}" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
        @endif
    </div>
    <div class="card-body">
        @if($errors->has('store'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ $errors->first('store') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->has('update'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ $errors->first('update') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ $formAction }}" method="POST" class="row g-2">
            @csrf
            @if($isEditing)
                @method('PATCH')
            @endif

            <div class="col-md-4">
                <label class="form-label">Schedule Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $defaultName) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Scope</label>
                <select name="scope" class="form-control" required>
                    @foreach(['monitoring', 'board', 'risks', 'heatmap', 'recap', 'overall'] as $scope)
                        <option value="{{ $scope }}" {{ old('scope', $defaultScope) === $scope ? 'selected' : '' }}>{{ strtoupper($scope) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Format</label>
                <select name="format" class="form-control" required>
                    <option value="pdf" {{ old('format', $defaultFormat) === 'pdf' ? 'selected' : '' }}>PDF</option>
                    <option value="xlsx" {{ old('format', $defaultFormat) === 'xlsx' ? 'selected' : '' }}>XLSX</option>
                    <option value="pptx" {{ old('format', $defaultFormat) === 'pptx' ? 'selected' : '' }}>PPTX</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Frequency</label>
                <select id="frequencySelect" name="frequency" class="form-control" required>
                    <option value="daily" {{ old('frequency', $defaultFrequency) === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ old('frequency', $defaultFrequency) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ old('frequency', $defaultFrequency) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Send Time</label>
                <input type="time" name="send_time" class="form-control" required value="{{ old('send_time', $defaultSendTime) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Timezone</label>
                <select name="timezone" class="form-control" required>
                    @foreach($timezones as $tz)
                        <option value="{{ $tz }}" {{ old('timezone', $defaultTimezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2" id="weeklyDayWrap">
                <label class="form-label">Day of Week</label>
                <select name="day_of_week" class="form-control">
                    @foreach([
                        0 => 'Sunday',
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                    ] as $dayValue => $dayLabel)
                        <option value="{{ $dayValue }}" {{ (string) old('day_of_week', $defaultDayOfWeek) === (string) $dayValue ? 'selected' : '' }}>{{ $dayLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2" id="monthlyDayWrap">
                <label class="form-label">Day of Month</label>
                <select name="day_of_month" class="form-control">
                    @for($d = 1; $d <= 28; $d++)
                        <option value="{{ $d }}" {{ (string) old('day_of_month', $defaultDayOfMonth) === (string) $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year (Optional)</label>
                <input
                    type="number"
                    min="2000"
                    max="2100"
                    name="year"
                    class="form-control"
                    value="{{ old('year', $editingFilters['year'] ?? '') }}"
                >
            </div>
            <div class="col-md-3">
                <label class="form-label">Risk Category (Optional)</label>
                <select name="risk_category_id" class="form-control">
                    <option value="">All</option>
                    @foreach($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            {{ (string) old('risk_category_id', $editingFilters['risk_category_id'] ?? '') === (string) $category->id ? 'selected' : '' }}
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Cause Category (Optional)</label>
                <select name="risk_cause_category_id" class="form-control">
                    <option value="">All</option>
                    @foreach($causeCategories as $causeCategory)
                        <option
                            value="{{ $causeCategory->id }}"
                            {{ (string) old('risk_cause_category_id', $editingFilters['risk_cause_category_id'] ?? '') === (string) $causeCategory->id ? 'selected' : '' }}
                        >
                            {{ $causeCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Risk Owner (Optional)</label>
                <select name="risk_owner" class="form-control">
                    <option value="">All</option>
                    @foreach($riskOwners as $owner)
                        @php
                            $ownerLabel = \App\Support\RiskOwnerAccess::displayLabel((string) $owner, (string) $owner);
                        @endphp
                        <option
                            value="{{ $owner }}"
                            {{ (string) old('risk_owner', $editingFilters['risk_owner'] ?? '') === (string) $owner ? 'selected' : '' }}
                        >
                            {{ $ownerLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Recipients</label>
                <input
                    type="text"
                    name="recipients"
                    class="form-control"
                    value="{{ old('recipients', $editingRecipients !== '' ? $editingRecipients : auth()->user()->email) }}"
                    placeholder="email1@company.com, email2@company.com"
                >
                <small class="text-muted">Pisahkan email dengan koma.</small>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                @if($isEditing)
                    <a href="{{ route('reports.scheduled.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                @endif
                <button type="submit" class="btn btn-primary btn-sm with-icon">
                    <i class="fas fa-save"></i> {{ $isEditing ? 'Update Schedule' : 'Save Schedule' }}
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <strong>Your Schedules</strong>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Scope</th>
                    <th>Format</th>
                    <th>Frequency</th>
                    <th>Next Run</th>
                    <th>Last Delivery</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                    @php
                        $scheduleRuns = $runHistory->get((int) $schedule->id, collect());
                        $latestRun = $scheduleRuns->first();
                        $nextRunUtc = $schedule->next_run_at;
                        $nextRunLocal = !is_null($nextRunUtc) ? $nextRunUtc->copy()->setTimezone($schedule->timezone) : null;
                        $latestRunAtLocal = !is_null(optional($latestRun)->ran_at)
                            ? $latestRun->ran_at->copy()->setTimezone($schedule->timezone)
                            : null;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $schedule->name }}</div>
                            <small class="text-muted">
                                {{ strtoupper($schedule->timezone) }} {{ $schedule->send_time }}
                                @if($schedule->frequency === 'weekly')
                                    | DOW {{ $schedule->day_of_week }}
                                @elseif($schedule->frequency === 'monthly')
                                    | DOM {{ $schedule->day_of_month }}
                                @endif
                            </small>
                        </td>
                        <td>{{ strtoupper($schedule->scope) }}</td>
                        <td>{{ strtoupper($schedule->format) }}</td>
                        <td>{{ ucfirst($schedule->frequency) }}</td>
                        <td>
                            @if($nextRunUtc)
                                <div>{{ $nextRunLocal ? $nextRunLocal->format('Y-m-d H:i:s') : '-' }} {{ $schedule->timezone }}</div>
                                <small class="text-muted">UTC: {{ $nextRunUtc->format('Y-m-d H:i:s') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($latestRun)
                                <div>
                                    <span class="badge {{ $latestRun->status === 'success' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($latestRun->status) }}
                                    </span>
                                    <span class="badge bg-light text-dark">{{ strtoupper($latestRun->trigger_type) }}</span>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    {{ $latestRunAtLocal ? $latestRunAtLocal->format('Y-m-d H:i:s') : '-' }} {{ $schedule->timezone }}
                                </small>
                                @if(!empty($latestRun->error_message))
                                    <small class="text-danger d-block mt-1">
                                        {{ \Illuminate\Support\Str::limit((string) $latestRun->error_message, 120) }}
                                    </small>
                                @endif
                                @if($scheduleRuns->count() > 1)
                                    <details class="mt-1">
                                        <summary class="small text-muted">History</summary>
                                        <div class="mt-1">
                                            @foreach($scheduleRuns->take(5) as $run)
                                                @php
                                                    $runAtLocal = !is_null($run->ran_at)
                                                        ? $run->ran_at->copy()->setTimezone($schedule->timezone)
                                                        : null;
                                                @endphp
                                                <div class="small">
                                                    {{ $runAtLocal ? $runAtLocal->format('Y-m-d H:i:s') : '-' }}
                                                    - {{ strtoupper($run->status) }}
                                                    ({{ strtoupper($run->trigger_type) }})
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            @else
                                <span class="text-muted">Belum pernah dijalankan.</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $schedule->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $schedule->is_active ? 'Active' : 'Paused' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('reports.scheduled.index', ['edit' => $schedule->id]) }}" class="btn btn-sm btn-outline-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('reports.scheduled.runNow', $schedule->id) }}" method="POST" onsubmit="return confirm('Run schedule ini sekarang?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-dark">Run Now</button>
                                </form>
                                <form action="{{ route('reports.scheduled.toggle', $schedule->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        {{ $schedule->is_active ? 'Pause' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('reports.scheduled.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('Delete schedule ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada scheduled report.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
    const frequencySelect = document.getElementById('frequencySelect');
    const weeklyWrap = document.getElementById('weeklyDayWrap');
    const monthlyWrap = document.getElementById('monthlyDayWrap');
    if (!frequencySelect || !weeklyWrap || !monthlyWrap) {
        return;
    }

    function syncFrequencyInputs() {
        const freq = String(frequencySelect.value || '').toLowerCase();
        weeklyWrap.style.display = freq === 'weekly' ? '' : 'none';
        monthlyWrap.style.display = freq === 'monthly' ? '' : 'none';
    }

    frequencySelect.addEventListener('change', syncFrequencyInputs);
    syncFrequencyInputs();
})();
</script>
@endsection
