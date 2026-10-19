@extends('layouts.tabler')

@section('title', 'Role & Access Control - ' . config('app.name', 'Raksantara'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <div class="page-pretitle text-muted mb-1">
            <i class="fas fa-user-shield me-1 text-primary"></i> App Management &amp; Security
        </div>
        <h1 class="page-title text-dark">Role &amp; Access Control</h1>
        <div class="text-muted mt-1">
            Kelola penugasan peran (RBAC), batas ruang lingkup organisasi, dan kontrol masa berlaku akses sementara.
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm with-icon">
            <i class="fas fa-users me-1"></i> User Management
        </a>
        <a href="{{ route('parameters.index') }}" class="btn btn-outline-secondary btn-sm with-icon">
            <i class="fas fa-sliders-h me-1"></i> Parameter
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row row-deck row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <span class="avatar avatar-sm bg-blue-lt me-2"><i class="fas fa-user-shield"></i></span>
                    <div class="subheader text-muted">Total Penugasan</div>
                </div>
                <div class="h2 mb-0 fw-bold">{{ $assignments->total() }}</div>
                <div class="text-muted small mt-1">Role assignments terdaftar</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <span class="avatar avatar-sm bg-green-lt me-2"><i class="fas fa-check-circle"></i></span>
                    <div class="subheader text-muted">Akses Aktif</div>
                </div>
                <div class="h2 mb-0 fw-bold">
                    {{ $assignments->filter(fn($a) => is_null($a->expires_at) || $a->expires_at->isFuture())->count() }}
                    <span class="text-muted fs-6 fw-normal">di halaman ini</span>
                </div>
                <div class="text-muted small mt-1">Hak akses berlaku saat ini</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <span class="avatar avatar-sm bg-purple-lt me-2" style="background-color: #ede9fe; color: #6d28d9;"><i class="fas fa-globe"></i></span>
                    <div class="subheader text-muted">Enterprise-Wide</div>
                </div>
                <div class="h2 mb-0 fw-bold">
                    {{ $assignments->filter(fn($a) => is_null($a->organizational_unit_id))->count() }}
                    <span class="text-muted fs-6 fw-normal">di halaman ini</span>
                </div>
                <div class="text-muted small mt-1">Cakupan seluruh enterprise</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <span class="avatar avatar-sm bg-yellow-lt me-2"><i class="fas fa-business-time"></i></span>
                    <div class="subheader text-muted">Temporary Access</div>
                </div>
                <div class="h2 mb-0 fw-bold">
                    {{ $assignments->filter(fn($a) => !is_null($a->expires_at))->count() }}
                    <span class="text-muted fs-6 fw-normal">di halaman ini</span>
                </div>
                <div class="text-muted small mt-1">Masa berlaku sementara</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0 d-flex align-items-center gap-2">
            <i class="fas fa-user-tag text-primary"></i>
            <span>Assign or Renew Role</span>
        </h3>
        <span class="badge bg-blue-lt">RBAC &amp; Organizational Scope</span>
    </div>
    <div class="card-body">
        <div class="text-muted small mb-3">
            Tugaskan peran GRC kepada pengguna dengan batasan cakupan organisasi tertentu atau seluruh enterprise. Tentukan <strong>Expiry Date</strong> untuk akses sementara (seperti auditor eksternal atau penugasan interim).
        </div>
        <form method="POST" action="{{ route('access-control.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">User <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                        <option value="">Pilih pengguna...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} · {{ $user->username }} ({{ $user->division ?: 'No Division' }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                        <option value="">Pilih peran (role)...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }} title="{{ $role->description }}">
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Organizational Scope</label>
                    <select name="organizational_unit_id" class="form-control @error('organizational_unit_id') is-invalid @enderror">
                        <option value="">Enterprise-wide (Seluruh Organisasi)</option>
                        @foreach($organizationalUnits as $unit)
                            <option value="{{ $unit->id }}" {{ old('organizational_unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->code }} · {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('organizational_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-hint">Kosongkan untuk akses ke semua unit organisasi</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Expiry Date <span class="text-muted small">(Opsional)</span></label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="form-control @error('expires_at') is-invalid @enderror">
                    @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-hint">Kosongkan jika akses permanen</div>
                </div>
                <div class="col-12 text-end pt-2">
                    <button class="btn btn-primary with-icon" type="submit">
                        <i class="fas fa-check-circle me-1"></i> Save Assignment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0 d-flex align-items-center gap-2">
            <i class="fas fa-table-list text-primary"></i>
            <span>Role Assignments Register</span>
        </h3>
        <span class="badge bg-secondary-lt">{{ $assignments->total() }} Total Penugasan</span>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-hover mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Organizational Scope</th>
                    <th>Assigned By</th>
                    <th>Expiry &amp; Validity</th>
                    <th>Status</th>
                    <th class="w-1 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    @php($isExpired = $assignment->expires_at && $assignment->expires_at->isPast())
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-sm bg-blue-lt">
                                    {{ strtoupper(substr(optional($assignment->user)->name ?? 'U', 0, 1)) }}
                                </span>
                                <div>
                                    <div class="fw-bold text-dark">{{ optional($assignment->user)->name ?: '-' }}</div>
                                    <div class="small text-muted">{{ optional($assignment->user)->username ?: '-' }}</div>
                                    @if(optional($assignment->user)->division)
                                        <span class="badge bg-secondary-lt mt-1">{{ optional($assignment->user)->division }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <span class="badge bg-blue-lt fw-bold">
                                    <i class="fas fa-shield-alt me-1"></i> {{ optional($assignment->role)->name ?: '-' }}
                                </span>
                            </div>
                            @if(optional($assignment->role)->description)
                                <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit($assignment->role->description, 60) }}</div>
                            @endif
                        </td>
                        <td>
                            @if($assignment->organizationalUnit)
                                <span class="badge bg-azure-lt" style="background-color: #e0f2fe; color: #0369a1;">
                                    <i class="fas fa-sitemap me-1"></i> {{ $assignment->organizationalUnit->code }} · {{ $assignment->organizationalUnit->name }}
                                </span>
                            @else
                                <span class="badge bg-purple-lt" style="background-color: #ede9fe; color: #6d28d9;">
                                    <i class="fas fa-globe me-1"></i> Enterprise-wide
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1 text-muted">
                                <i class="fas fa-user-check small"></i>
                                <span>{{ optional($assignment->assigner)->name ?: 'System migration' }}</span>
                            </div>
                            @if($assignment->created_at)
                                <div class="small text-muted">{{ $assignment->created_at->format('d M Y H:i') }}</div>
                            @endif
                        </td>
                        <td>
                            @if($assignment->expires_at)
                                <div>
                                    <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                    <strong>{{ $assignment->expires_at->format('d M Y') }}</strong>
                                </div>
                                <div class="small {{ $isExpired ? 'text-danger' : 'text-muted' }}">
                                    {{ $assignment->expires_at->diffForHumans() }}
                                </div>
                            @else
                                <span class="badge bg-secondary-lt">
                                    <i class="fas fa-infinity me-1"></i> Permanent
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($isExpired)
                                <span class="badge bg-danger text-white">
                                    <i class="fas fa-times-circle me-1"></i> Expired
                                </span>
                            @else
                                <span class="badge bg-green-lt">
                                    <i class="fas fa-check-circle me-1"></i> Active
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('access-control.destroy', $assignment->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin mencabut (revoke) role assignment ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm with-icon">
                                    <i class="fas fa-user-minus me-1"></i> Revoke
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty">
                                <div class="empty-icon text-muted mb-2">
                                    <i class="fas fa-user-shield fa-2x"></i>
                                </div>
                                <p class="empty-title fw-bold">Belum ada role assignment yang terdaftar</p>
                                <p class="empty-subtitle text-muted">Gunakan form di atas untuk menugaskan role ke pengguna.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($assignments->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-between">
            <div class="text-muted small">
                Showing {{ $assignments->firstItem() ?? 0 }} to {{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }} entries
            </div>
            <div>
                {{ $assignments->links() }}
            </div>
        </div>
    @endif
</div>
@stop
