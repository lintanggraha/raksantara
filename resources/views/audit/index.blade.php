@extends('layouts.tabler')

@section('title', 'Audit Trail')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Audit Trail</h1>
    <a href="{{ route('risks.index') }}" class="btn btn-secondary btn-sm with-icon">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Event</label>
                <select name="event" class="form-control">
                    <option value="">All Events</option>
                    @foreach($events as $eventName => $eventLabel)
                        <option value="{{ $eventName }}" {{ $event === $eventName ? 'selected' : '' }}>{{ $eventLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Activity</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log['time'] }}</td>
                        <td>{{ $log['actor'] }}</td>
                        <td>{{ $log['activity'] }}</td>
                        <td>{{ $log['details'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">No audit logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $logs->links() }}
    </div>
</div>
@stop
