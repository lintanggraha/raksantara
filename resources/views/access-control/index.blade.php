@extends('adminlte::page')

@section('title', 'Access Control')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Access Control</h1>
        <div class="text-muted">Manage role assignments, organizational scope, and temporary access.</div>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-users"></i> User Management</a>
</div>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card mb-3">
    <div class="card-header"><strong>Assign or Renew Role</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('access-control.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4"><label class="form-label">User</label><select name="user_id" class="form-control" required><option value="">Select user</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }} · {{ $user->username }} · {{ $user->division ?: '-' }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Role</label><select name="role_id" class="form-control" required><option value="">Select role</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Organizational Scope</label><select name="organizational_unit_id" class="form-control"><option value="">Enterprise-wide</option>@foreach($organizationalUnits as $unit)<option value="{{ $unit->id }}">{{ $unit->code }} · {{ $unit->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Expiry Date</label><input type="date" name="expires_at" class="form-control"><button class="btn btn-primary w-100 mt-2" type="submit">Save Assignment</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>Role Assignments</strong></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead><tr><th>User</th><th>Role</th><th>Scope</th><th>Assigned By</th><th>Expiry</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($assignments as $assignment)
                    @php($isExpired = $assignment->expires_at && $assignment->expires_at->isPast())
                    <tr>
                        <td><strong>{{ optional($assignment->user)->name ?: '-' }}</strong><div class="small text-muted">{{ optional($assignment->user)->division ?: '-' }}</div></td>
                        <td>{{ optional($assignment->role)->name ?: '-' }}</td>
                        <td>{{ $assignment->organizationalUnit ? $assignment->organizationalUnit->code . ' · ' . $assignment->organizationalUnit->name : 'Enterprise-wide' }}</td>
                        <td>{{ optional($assignment->assigner)->name ?: 'System migration' }}</td>
                        <td>{{ optional($assignment->expires_at)->format('d M Y') ?: 'No expiry' }}</td>
                        <td><span class="badge badge-{{ $isExpired ? 'secondary' : 'success' }}">{{ $isExpired ? 'Expired' : 'Active' }}</span></td>
                        <td><form method="POST" action="{{ route('access-control.destroy', $assignment->id) }}" onsubmit="return confirm('Revoke this role assignment?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-xs">Revoke</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No explicit role assignment exists yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($assignments->hasPages())<div class="card-footer">{{ $assignments->links() }}</div>@endif
</div>
@stop
