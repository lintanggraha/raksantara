{{--
    Sticky top bar: where am I, search (command palette), create, and account.
    The sidebar's own user block is hidden on desktop once this bar is present.
--}}
@php
    $rkViewer = auth()->user();
    $rkAppName = config('app.name', 'Raksantara');
    $rkTitle = trim(preg_replace('/\s*[-|–]\s*' . preg_quote($rkAppName, '/') . '\s*$/u', '', (string) $__env->yieldContent('title', $rkAppName)));
    $rkInitial = strtoupper(mb_substr($rkViewer->name ?: $rkViewer->username, 0, 1));
    $rkSubtitle = $rkViewer->isSuperadmin()
        ? 'Superadmin'
        : ($rkViewer->isComplianceDivision() ? 'Compliance' : \App\Support\RiskOwnerAccess::displayLabel($rkViewer->division, 'User'));
    $rkCanCreate = !$rkViewer->isSuperadmin() && $rkViewer->isDivisionVerified();
@endphp
<div class="rk-topbar d-print-none">
    <div class="rk-topbar-inner">
        <nav class="rk-crumbs" aria-label="Lokasi halaman">
            <a href="{{ route('dashboard') }}">{{ $rkAppName }}</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">{{ $rkTitle !== '' ? $rkTitle : 'Dashboard' }}</span>
        </nav>

        <button type="button" class="rk-search-trigger" data-rk-palette-open aria-haspopup="dialog" aria-keyshortcuts="Control+K Meta+K">
            <i class="fas fa-search" aria-hidden="true"></i>
            <span class="label">Cari menu atau aksi…</span>
            <span class="rk-kbd" aria-hidden="true"><span data-rk-mod>Ctrl</span> K</span>
            <span class="visually-hidden">Buka pencarian</span>
        </button>

        <div class="rk-topbar-actions">
            @if($rkCanCreate)
                <div class="dropdown">
                    <button type="button" class="rk-icon-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plus" aria-hidden="true"></i><span class="label">Baru</span>
                        <span class="visually-hidden">Buat data baru</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header">Buat baru</div>
                        <a class="dropdown-item" href="{{ route('risks.create') }}"><i class="fas fa-shield-alt" aria-hidden="true"></i> Risk</a>
                        <a class="dropdown-item" href="{{ route('incidents.create') }}"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Insiden / temuan</a>
                        <a class="dropdown-item" href="{{ route('kris.index') }}"><i class="fas fa-tachometer-alt" aria-hidden="true"></i> KRI</a>
                    </div>
                </div>
            @endif

            <div class="dropdown">
                <button type="button" class="rk-user-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun {{ $rkViewer->name }}">
                    <span class="rk-avatar" aria-hidden="true">{{ $rkInitial }}</span>
                    <span class="rk-user-meta">
                        <strong>{{ $rkViewer->name ?: $rkViewer->username }}</strong>
                        <span>{{ $rkSubtitle }}</span>
                    </span>
                    <i class="fas fa-chevron-down text-muted small" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="dropdown-header">{{ '@' . $rkViewer->username }}</div>
                    <a class="dropdown-item" href="{{ route('profile.index') }}"><i class="fas fa-user" aria-hidden="true"></i> Profil</a>
                    <a class="dropdown-item" href="{{ route('change.password') }}"><i class="fas fa-key" aria-hidden="true"></i> Ganti password</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
