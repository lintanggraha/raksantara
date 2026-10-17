{{-- Command palette, toasts and the navigation progress bar (driven by public/js/raksantara-ui.js). --}}
@php
    $rkViewer = auth()->user();
    $rkActions = [];
    if ($rkViewer && !$rkViewer->isSuperadmin() && $rkViewer->isDivisionVerified()) {
        $rkActions[] = ['label' => 'Tambah risk baru', 'hint' => 'Risk register', 'icon' => 'fas fa-plus', 'href' => route('risks.create')];
        $rkActions[] = ['label' => 'Catat insiden atau temuan', 'hint' => 'Incident management', 'icon' => 'fas fa-exclamation-triangle', 'href' => route('incidents.create')];
    }
    if ($rkViewer) {
        $rkActions[] = ['label' => 'Ganti password', 'hint' => 'Akun', 'icon' => 'fas fa-key', 'href' => route('change.password')];
        $rkActions[] = ['label' => 'Profil saya', 'hint' => 'Akun', 'icon' => 'fas fa-user', 'href' => route('profile.index')];
    }
    $rkFlash = array_values(array_filter([
        session('success') ? ['type' => 'success', 'title' => 'Berhasil', 'text' => (string) session('success')] : null,
        session('error') ? ['type' => 'error', 'title' => 'Gagal', 'text' => (string) session('error')] : null,
    ]));
@endphp
<div class="rk-progress" data-rk-progress aria-hidden="true"></div>

<div class="rk-palette" data-rk-palette hidden>
    <div class="rk-palette-backdrop" data-rk-palette-close></div>
    <div class="rk-palette-panel" role="dialog" aria-modal="true" aria-label="Pencarian cepat">
        <div class="rk-palette-search">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="text" data-rk-palette-input placeholder="Ketik nama halaman atau aksi…" autocomplete="off"
                   role="combobox" aria-expanded="true" aria-controls="rk-palette-list" aria-autocomplete="list">
            <span class="rk-kbd" aria-hidden="true">Esc</span>
        </div>
        <ul class="rk-palette-list" id="rk-palette-list" role="listbox" data-rk-palette-list></ul>
        <div class="rk-palette-foot" aria-hidden="true">
            <span><span class="rk-kbd">↑</span> <span class="rk-kbd">↓</span> pilih</span>
            <span><span class="rk-kbd">Enter</span> buka</span>
            <span><span class="rk-kbd">Esc</span> tutup</span>
        </div>
    </div>
</div>

<div class="rk-toasts" data-rk-toasts role="status" aria-live="polite"></div>
<script type="application/json" data-rk-actions>@json($rkActions)</script>
<script type="application/json" data-rk-flash>@json($rkFlash)</script>
