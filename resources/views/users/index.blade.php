@extends('layouts.tabler')

@section('title', 'User Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="mb-0">User Management</h1>
    <a href="{{ route('risks.index') }}" class="btn btn-secondary btn-sm with-icon">
        <i class="fas fa-arrow-left"></i> Back to Risk Register
    </a>
</div>
@stop

@section('content')
@if(session('temporary_password'))
    <div class="alert alert-warning">
        <div><strong>Temporary Password Generated</strong></div>
        <div>User: <strong>{{ session('temporary_password_username') }}</strong></div>
        <div>Password sementara: <code>{{ session('temporary_password') }}</code></div>
        <div class="small text-muted mt-1">
            Simpan password ini sekarang. Setelah halaman di-refresh, password tidak ditampilkan lagi.
        </div>
    </div>
@endif

<div class="card shadow">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Search User</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Name / username / email">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-vcenter mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Division Status</th>
                    <th>Division Validation</th>
                    <th>Superadmin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    @php
                        $divisionDisplay = $user->division ?: '-';
                        $pendingDisplay = $user->pending_division ?: '-';
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $user->name }}</div>
                            <div class="text-muted">{{ $user->username }}</div>
                            <div class="text-muted">{{ $user->email }}</div>
                            <div class="text-muted small">Joined: {{ optional($user->created_at)->format('Y-m-d H:i') }}</div>
                        </td>
                        <td>
                            <div><span class="text-muted">Current:</span> {{ $divisionDisplay }}</div>
                            <div><span class="text-muted">Requested:</span> {{ $pendingDisplay }}</div>
                            <div class="mt-1">
                                @if($user->isDivisionVerified())
                                    <span class="badge bg-green-lt">Verified</span>
                                @else
                                    <span class="badge bg-yellow-lt">Pending</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <form action="{{ route('users.updateDivision', $user->id) }}" method="POST" class="d-flex gap-2 align-items-center">
                                @csrf
                                @method('PUT')
                                @php
                                    $selectedDivision = old('division', $user->division ?: $user->pending_division);
                                @endphp
                                <select name="division" class="form-control form-control-sm">
                                    @foreach($divisionOptions as $division)
                                        <option value="{{ $division }}" {{ $selectedDivision === $division ? 'selected' : '' }}>
                                            {{ $division }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('users.updateSuperadmin', $user->id) }}" method="POST" class="d-flex gap-2 align-items-center">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_superadmin" value="{{ $user->is_superadmin ? 0 : 1 }}">
                                @if($user->is_superadmin)
                                    <span class="badge bg-blue-lt">Yes</span>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Revoke</button>
                                @else
                                    <span class="badge bg-secondary-lt">No</span>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Promote</button>
                                @endif
                            </form>
                            <form action="{{ route('users.resetPassword', $user->id) }}" method="POST" class="mt-2">
                                @csrf
                                @method('PUT')
                                @if((int) auth()->id() === (int) $user->id)
                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" disabled>
                                        Reset Password (Self Disabled)
                                    </button>
                                @else
                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-warning w-100"
                                        onclick="return confirm('Reset password user ini? Sistem akan membuat temporary password baru.')"
                                    >
                                        Reset Password
                                    </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    @endif
</div>
@stop
