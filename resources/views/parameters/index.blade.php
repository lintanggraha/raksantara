@extends('layouts.tabler')

@section('title', 'Parameter Management')

@section('css')
<style>
    .parameter-tabs-card {
        border: 0;
        background: transparent;
        box-shadow: none;
        overflow: visible;
    }
    .parameter-tabs-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0 0 0.75rem;
    }
    .parameter-tabs-copy h2 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }
    .parameter-tabs-copy p {
        margin: 0.2rem 0 0;
        color: #64748b;
        font-size: 0.8125rem;
    }
    .parameter-tabs-nav {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.9rem 1rem;
        padding: 0;
        overflow: visible;
    }
    .parameter-tabs-nav .nav-link {
        display: flex;
        width: 100%;
        min-width: 0;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.75rem 0.2rem 0.9rem;
        border: 0;
        border-top: 3px solid #cbd5e1;
        border-radius: 0;
        background: transparent;
        color: #475569;
        text-align: left;
        box-shadow: none;
        transition: color 0.15s ease, border-color 0.15s ease;
    }
    .parameter-tabs-nav .nav-link:hover,
    .parameter-tabs-nav .nav-link:focus {
        border-top-color: #60a5fa;
        color: #0b57d0;
        background: transparent;
    }
    .parameter-tabs-nav .nav-link.active {
        border-top-color: #0b57d0;
        background: transparent;
        color: #0b57d0;
        box-shadow: none;
    }
    .parameter-tab-number {
        width: 1.75rem;
        height: 1.75rem;
        flex: 0 0 1.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #e2e8f0;
        color: inherit;
        font-weight: 700;
        font-size: 0.8rem;
    }
    .parameter-tabs-nav .nav-link.active .parameter-tab-number {
        background: #dbeafe;
    }
    .parameter-tab-meta {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        min-width: 0;
    }
    .parameter-tab-label {
        font-weight: 700;
        line-height: 1.25;
        font-size: 0.86rem;
    }
    .parameter-tab-count {
        font-size: 0.72rem;
        color: inherit;
        opacity: 0.72;
    }
    .parameter-pane-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
    }
    .parameter-pane-card .card-header {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    .parameter-pane-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }
    .parameter-pane-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #2563eb;
        margin-bottom: 0.35rem;
    }
    .parameter-pane-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .parameter-pane-desc {
        margin: 0.35rem 0 0;
        color: #64748b;
        font-size: 0.875rem;
        max-width: 760px;
    }
    .parameter-pane-badge {
        flex: 0 0 auto;
        align-self: center;
        border-radius: 999px;
        padding: 0.5rem 0.85rem;
        background: #e0ecff;
        color: #0b57d0;
        font-weight: 700;
        font-size: 0.8rem;
    }
    .parameter-module-card {
        border: 1px solid #dbe3ef;
        border-radius: 20px;
        padding: 1.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
    }
    .parameter-module-icon {
        width: 3rem;
        height: 3rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: #dbeafe;
        color: #0b57d0;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }
    .parameter-module-card h3 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
    }
    .parameter-module-card p {
        margin: 0.6rem 0 1rem;
        color: #64748b;
    }
    .parameter-module-stats {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: #eef4ff;
        color: #0b57d0;
        font-size: 0.8rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .parameter-inline-note {
        margin-top: 0.5rem;
        color: #64748b;
        font-size: 0.8rem;
    }
    .parameter-category-block + .parameter-category-block {
        margin-top: 1rem;
    }
    @media (max-width: 1199.98px) {
        .parameter-tabs-nav {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (max-width: 991.98px) {
        .parameter-tabs-nav {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (max-width: 767.98px) {
        .parameter-tabs-head {
            padding-bottom: 0;
        }
        .parameter-tabs-nav {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .parameter-tabs-nav .nav-link {
            padding-top: 0.65rem;
            padding-bottom: 0.75rem;
        }
        .parameter-tab-label i {
            display: none;
        }
        .parameter-pane-header {
            flex-direction: column;
        }
    }
</style>
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">Parameter Management</h1>
        <div class="text-muted mt-1">Master data sekarang dipisah per nomor supaya superadmin tidak perlu scroll panjang terus.</div>
    </div>
    <a href="{{ route('risks.monitoring') }}" class="btn btn-outline-secondary btn-sm with-icon">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>
@stop

@section('content')
@php
    $parameterTabs = [
        ['key' => 'risk-category', 'number' => 1, 'label' => 'Risk Category', 'icon' => 'fas fa-folder-open', 'count' => $riskCategories->count(), 'countLabel' => 'items'],
        ['key' => 'risk-owner', 'number' => 2, 'label' => 'Risk Owner', 'icon' => 'fas fa-user-tie', 'count' => $riskOwners->count(), 'countLabel' => 'owners'],
        ['key' => 'corporate-risk-appetite', 'number' => 3, 'label' => 'Corporate Risk Appetite', 'icon' => 'fas fa-shield-alt', 'count' => $riskAppetiteCount, 'countLabel' => 'frameworks'],
        ['key' => 'cause-category', 'number' => 4, 'label' => 'Cause Category', 'icon' => 'fas fa-sitemap', 'count' => $causeCategories->count(), 'countLabel' => 'items'],
        ['key' => 'third-party-vendor', 'number' => 5, 'label' => 'Third-Party Vendor', 'icon' => 'fas fa-handshake', 'count' => $vendorCount, 'countLabel' => 'vendors'],
        ['key' => 'probability-description', 'number' => 6, 'label' => 'Probability Description', 'icon' => 'fas fa-wave-square', 'count' => $probabilityScales->count(), 'countLabel' => 'scores'],
        ['key' => 'impact-category', 'number' => 7, 'label' => 'Impact Category', 'icon' => 'fas fa-bullseye', 'count' => $impactCategories->count(), 'countLabel' => 'items'],
        ['key' => 'impact-description', 'number' => 8, 'label' => 'Impact Description', 'icon' => 'fas fa-align-left', 'count' => $impactCategories->sum(fn ($category) => $category->scales->count()), 'countLabel' => 'descriptions'],
        ['key' => 'mitigation-playbook', 'number' => 9, 'label' => 'Mitigation Playbook', 'icon' => 'fas fa-book-open', 'count' => $playbookCount, 'countLabel' => 'playbooks'],
        ['key' => 'group-radar-description', 'number' => 10, 'label' => 'Group Radar Description', 'icon' => 'fas fa-satellite-dish', 'count' => $radarGroups->count(), 'countLabel' => 'groups'],
    ];
@endphp

<div data-parameter-management data-parameter-active-tab="{{ $activeTab }}">
    <div class="card parameter-tabs-card mb-3">
        <div class="parameter-tabs-head">
            <div class="parameter-tabs-copy">
                <h2>Pick the parameter block you want to edit</h2>
                <p>Klik nomor yang dibutuhkan. Semua form add dan edit tetap ada, tapi sekarang dipisah per tab biar lebih cepat dan lebih rapi.</p>
            </div>
        </div>
        <div class="nav nav-pills parameter-tabs-nav" id="parameter-management-tabs" role="tablist" aria-label="Parameter tabs">
            @foreach($parameterTabs as $tab)
                <button
                    id="parameter-tab-{{ $tab['key'] }}"
                    class="nav-link {{ $activeTab === $tab['key'] ? 'active' : '' }}"
                    data-parameter-tab-target="{{ $tab['key'] }}"
                    data-bs-toggle="tab"
                    data-bs-target="#parameter-pane-{{ $tab['key'] }}"
                    type="button"
                    role="tab"
                    aria-controls="parameter-pane-{{ $tab['key'] }}"
                    aria-selected="{{ $activeTab === $tab['key'] ? 'true' : 'false' }}"
                >
                    <span class="parameter-tab-number">{{ $tab['number'] }}</span>
                    <span class="parameter-tab-meta">
                        <span class="parameter-tab-label">
                            <i class="{{ $tab['icon'] }} me-2"></i>{{ $tab['label'] }}
                        </span>
                        <span class="parameter-tab-count">{{ $tab['count'] }} {{ $tab['countLabel'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="tab-content" id="parameter-management-tab-content">
        <div
            class="tab-pane fade {{ $activeTab === 'risk-category' ? 'show active' : '' }}"
            id="parameter-pane-risk-category"
            role="tabpanel"
            aria-labelledby="parameter-tab-risk-category"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-folder-open"></i> Parameter 1</div>
                            <h3 class="parameter-pane-title">Risk Category</h3>
                            <p class="parameter-pane-desc">Kelola daftar kategori utama untuk klasifikasi risk register dan form input risk.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $riskCategories->count() }} categories</div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('parameters.risk-categories.store') }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Add Risk Category</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riskCategories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td class="text-end">
                                            <details>
                                                <summary class="btn btn-outline-primary btn-sm">Edit</summary>
                                                <form method="POST" action="{{ route('parameters.risk-categories.update', $category->id) }}" class="mt-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                                        <input type="text" name="name" value="{{ $category->name }}" class="form-control" style="max-width: 320px;" maxlength="255" required>
                                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                    </div>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-muted">No risk categories yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade {{ $activeTab === 'risk-owner' ? 'show active' : '' }}"
            id="parameter-pane-risk-owner"
            role="tabpanel"
            aria-labelledby="parameter-tab-risk-owner"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-user-tie"></i> Parameter 2</div>
                            <h3 class="parameter-pane-title">Risk Owner</h3>
                            <p class="parameter-pane-desc">Tambah risk owner baru, atur urutannya, dan aktif/nonaktifkan tanpa memutus relasi data lama.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $riskOwners->count() }} owners</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Risk owner bisa ditambah dan diaktif/nonaktifkan. Nama existing sengaja tidak di-rename agar tidak memutus relasi string pada risk, user division, dan playbook lama.
                    </div>

                    <form method="POST" action="{{ route('parameters.risk-owners.store') }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Owner Label</label>
                            <input type="text" name="name" class="form-control" maxlength="255" placeholder="Contoh: OP - NEW OPERATIONS UNIT" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" min="0" max="100000" value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label d-block">Active</label>
                            <label class="form-check form-switch mt-2">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                <span class="form-check-label">Active</span>
                            </label>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riskOwners as $riskOwner)
                                    <tr>
                                        <td>{{ $riskOwner->name }}</td>
                                        <td>{{ $riskOwner->sort_order }}</td>
                                        <td>
                                            <span class="badge {{ $riskOwner->is_active ? 'bg-success-lt' : 'bg-secondary-lt' }}">
                                                {{ $riskOwner->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('parameters.risk-owners.update', $riskOwner->id) }}" class="d-inline-flex flex-wrap gap-2 justify-content-end">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="sort_order" value="{{ $riskOwner->sort_order }}" class="form-control form-control-sm" style="width: 100px;" min="0" max="100000">
                                                <input type="hidden" name="is_active" value="0">
                                                <label class="form-check form-switch mt-2">
                                                    <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ $riskOwner->is_active ? 'checked' : '' }}>
                                                </label>
                                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted">No risk owners yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade {{ $activeTab === 'corporate-risk-appetite' ? 'show active' : '' }}"
            id="parameter-pane-corporate-risk-appetite"
            role="tabpanel"
            aria-labelledby="parameter-tab-corporate-risk-appetite"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-shield-alt"></i> Parameter 3</div>
                            <h3 class="parameter-pane-title">Corporate Risk Appetite</h3>
                            <p class="parameter-pane-desc">Parameter ini dikelola dari modul dedicated karena strukturnya berupa framework lengkap, threshold, dan linkage ke risk.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $riskAppetiteCount }} frameworks</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="parameter-module-card">
                        <div class="parameter-module-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Corporate Risk Appetite Module</h3>
                        <p>Masuk ke modul dedicated untuk tambah, edit, dan review framework appetite yang dipakai di risk form dan threshold scanner.</p>
                        <div class="parameter-module-stats">
                            <i class="fas fa-layer-group"></i>
                            <span>{{ $riskAppetiteCount }} active frameworks listed</span>
                        </div>
                        <div>
                            <a href="{{ route('risk-appetites.index') }}" class="btn btn-primary with-icon">
                                <i class="fas fa-arrow-right"></i> Open Corporate Risk Appetite
                            </a>
                        </div>
                        <div class="parameter-inline-note">Nomor 3 sengaja tetap jadi tab sendiri supaya urutan parameter di sisi superadmin tetap konsisten.</div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="tab-pane fade {{ $activeTab === 'cause-category' ? 'show active' : '' }}"
            id="parameter-pane-cause-category"
            role="tabpanel"
            aria-labelledby="parameter-tab-cause-category"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-sitemap"></i> Parameter 4</div>
                            <h3 class="parameter-pane-title">Cause Category</h3>
                            <p class="parameter-pane-desc">Kelola kategori penyebab yang muncul di form risk untuk membantu klasifikasi akar masalah.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $causeCategories->count() }} categories</div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('parameters.cause-categories.store') }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label">Cause Category Name</label>
                            <input type="text" name="name" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Add Cause Category</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($causeCategories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td class="text-end">
                                            <details>
                                                <summary class="btn btn-outline-primary btn-sm">Edit</summary>
                                                <form method="POST" action="{{ route('parameters.cause-categories.update', $category->id) }}" class="mt-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                                        <input type="text" name="name" value="{{ $category->name }}" class="form-control" style="max-width: 320px;" maxlength="255" required>
                                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                    </div>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-muted">No cause categories yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade {{ $activeTab === 'third-party-vendor' ? 'show active' : '' }}"
            id="parameter-pane-third-party-vendor"
            role="tabpanel"
            aria-labelledby="parameter-tab-third-party-vendor"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-handshake"></i> Parameter 5</div>
                            <h3 class="parameter-pane-title">Third-Party Vendor</h3>
                            <p class="parameter-pane-desc">Vendor disimpan di modul khusus karena butuh field operasional yang lebih panjang daripada parameter biasa.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $vendorCount }} vendors</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="parameter-module-card">
                        <div class="parameter-module-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3>Third-Party Vendor Module</h3>
                        <p>Gunakan modul vendor management untuk mengelola daftar vendor pihak ketiga yang dipakai di risk form dan monitoring vendor.</p>
                        <div class="parameter-module-stats">
                            <i class="fas fa-building"></i>
                            <span>{{ $vendorCount }} vendors available</span>
                        </div>
                        <div>
                            <a href="{{ route('vendors.index') }}" class="btn btn-primary with-icon">
                                <i class="fas fa-arrow-right"></i> Open Third-Party Vendor
                            </a>
                        </div>
                        <div class="parameter-inline-note">Nomor 5 tetap muncul di sini biar urutan parameter superadmin tidak putus.</div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade {{ $activeTab === 'probability-description' ? 'show active' : '' }}"
            id="parameter-pane-probability-description"
            role="tabpanel"
            aria-labelledby="parameter-tab-probability-description"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-wave-square"></i> Parameter 6</div>
                            <h3 class="parameter-pane-title">Probability Description</h3>
                            <p class="parameter-pane-desc">Atur deskripsi probabilitas per score, urutan tampil, dan status aktifnya untuk dropdown analisis risk.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $probabilityScales->count() }} scores</div>
                    </div>
                </div>
                <div class="card-body">
                    @php($existingProbabilityScores = $probabilityScales->pluck('score')->map(fn($score) => (int) $score)->all())
                    <form method="POST" action="{{ route('parameters.probability-scales.store') }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-2">
                            <label class="form-label">Score</label>
                            <select name="score" class="form-control" required>
                                <option value="">Select score</option>
                                @for($score = 1; $score <= 5; $score++)
                                    @if(!in_array($score, $existingProbabilityScores, true))
                                        <option value="{{ $score }}">{{ $score }}</option>
                                    @endif
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" maxlength="255" placeholder="Contoh: Mungkin terjadi sesekali" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" min="0" max="100000" value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label d-block">Active</label>
                            <label class="form-check form-switch mt-2">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                <span class="form-check-label">Active</span>
                            </label>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small mb-2">Nomor score akan tampil otomatis di dropdown risk form.</div>
                            <button type="submit" class="btn btn-primary">Add Probability Description</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Score</th>
                                    <th>Description</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($probabilityScales as $scale)
                                    <tr>
                                        <td>{{ $scale->score }}</td>
                                        <td>{{ $scale->description }}</td>
                                        <td>{{ $scale->sort_order }}</td>
                                        <td>
                                            <span class="badge {{ $scale->is_active ? 'bg-success-lt' : 'bg-secondary-lt' }}">
                                                {{ $scale->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <details>
                                                <summary class="btn btn-outline-primary btn-sm">Edit</summary>
                                                <form method="POST" action="{{ route('parameters.probability-scales.update', $scale->id) }}" class="mt-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="row g-2 justify-content-end">
                                                        <div class="col-md-6">
                                                            <input type="text" name="description" value="{{ $scale->description }}" class="form-control" maxlength="255" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="number" name="sort_order" value="{{ $scale->sort_order }}" class="form-control" min="0" max="100000">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="hidden" name="is_active" value="0">
                                                            <label class="form-check form-switch mt-2">
                                                                <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ $scale->is_active ? 'checked' : '' }}>
                                                                <span class="form-check-label">Active</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted">No probability descriptions yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="tab-pane fade {{ $activeTab === 'impact-category' ? 'show active' : '' }}"
            id="parameter-pane-impact-category"
            role="tabpanel"
            aria-labelledby="parameter-tab-impact-category"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-bullseye"></i> Parameter 7</div>
                            <h3 class="parameter-pane-title">Impact Category</h3>
                            <p class="parameter-pane-desc">Kelola kelompok impact yang akan menjadi payung untuk impact description dan scoring residual/inherent.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $impactCategories->count() }} categories</div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('parameters.impact-categories.store') }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label">Impact Category Name</label>
                            <input type="text" name="name" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Add Impact Category</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Descriptions</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($impactCategories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->scales->count() }}</td>
                                        <td class="text-end">
                                            <details>
                                                <summary class="btn btn-outline-primary btn-sm">Edit</summary>
                                                <form method="POST" action="{{ route('parameters.impact-categories.update', $category->id) }}" class="mt-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                                        <input type="text" name="name" value="{{ $category->name }}" class="form-control" style="max-width: 320px;" maxlength="255" required>
                                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                    </div>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted">No impact categories yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade {{ $activeTab === 'impact-description' ? 'show active' : '' }}"
            id="parameter-pane-impact-description"
            role="tabpanel"
            aria-labelledby="parameter-tab-impact-description"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-align-left"></i> Parameter 8</div>
                            <h3 class="parameter-pane-title">Impact Description</h3>
                            <p class="parameter-pane-desc">Kelola isi description per impact category dan score tanpa perlu bolak-balik ke bawah halaman.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $impactCategories->sum(fn ($category) => $category->scales->count()) }} descriptions</div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('parameters.impact-scales.store') }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Impact Category</label>
                            <select name="impact_category_id" class="form-control" required>
                                <option value="">Select category</option>
                                @foreach($impactCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Score</label>
                            <select name="score" class="form-control" required>
                                @for($score = 1; $score <= 5; $score++)
                                    <option value="{{ $score }}">{{ $score }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Add Impact Description</button>
                        </div>
                    </form>

                    @forelse($impactCategories as $category)
                        <div class="border rounded p-3 parameter-category-block">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <div class="fw-bold">{{ $category->name }}</div>
                                <span class="badge bg-blue-lt">{{ $category->scales->count() }} descriptions</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-vcenter mb-0">
                                    <thead>
                                        <tr>
                                            <th>Score</th>
                                            <th>Description</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($category->scales as $scale)
                                            <tr>
                                                <td>{{ $scale->score }}</td>
                                                <td>{{ $scale->description }}</td>
                                                <td class="text-end">
                                                    <details>
                                                        <summary class="btn btn-outline-primary btn-sm">Edit</summary>
                                                        <form method="POST" action="{{ route('parameters.impact-scales.update', $scale->id) }}" class="mt-2">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="row g-2 justify-content-end">
                                                                <div class="col-md-4">
                                                                    <select name="impact_category_id" class="form-control" required>
                                                                        @foreach($impactCategories as $impactCategory)
                                                                            <option value="{{ $impactCategory->id }}" {{ (int) $scale->impact_category_id === (int) $impactCategory->id ? 'selected' : '' }}>
                                                                                {{ $impactCategory->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <select name="score" class="form-control" required>
                                                                        @for($score = 1; $score <= 5; $score++)
                                                                            <option value="{{ $score }}" {{ (int) $scale->score === $score ? 'selected' : '' }}>{{ $score }}</option>
                                                                        @endfor
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <input type="text" name="description" value="{{ $scale->description }}" class="form-control" maxlength="255" required>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </details>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-muted">No impact descriptions yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No impact categories available yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade {{ $activeTab === 'mitigation-playbook' ? 'show active' : '' }}"
            id="parameter-pane-mitigation-playbook"
            role="tabpanel"
            aria-labelledby="parameter-tab-mitigation-playbook"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-book-open"></i> Parameter 9</div>
                            <h3 class="parameter-pane-title">Mitigation Playbook</h3>
                            <p class="parameter-pane-desc">Playbook punya form dan checklist yang lebih kaya, jadi pengelolaannya tetap lewat modul dedicated.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $playbookCount }} playbooks</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="parameter-module-card">
                        <div class="parameter-module-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3>Mitigation Playbook Module</h3>
                        <p>Buka modul playbook untuk mengelola template mitigasi, PIC default, target hari, next action, dan checklist evidence.</p>
                        <div class="parameter-module-stats">
                            <i class="fas fa-list-check"></i>
                            <span>{{ $playbookCount }} playbooks ready to use</span>
                        </div>
                        <div>
                            <a href="{{ route('treatment-templates.index') }}" class="btn btn-primary with-icon">
                                <i class="fas fa-arrow-right"></i> Open Mitigation Playbook
                            </a>
                        </div>
                        <div class="parameter-inline-note">Nomor 9 tetap dipertahankan agar urutan parameter di menu superadmin tetap lengkap dari 1 sampai 10.</div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade {{ $activeTab === 'group-radar-description' ? 'show active' : '' }}"
            id="parameter-pane-group-radar-description"
            role="tabpanel"
            aria-labelledby="parameter-tab-group-radar-description"
        >
            <div class="card parameter-pane-card">
                <div class="card-header">
                    <div class="parameter-pane-header">
                        <div>
                            <div class="parameter-pane-kicker"><i class="fas fa-satellite-dish"></i> Parameter 10</div>
                            <h3 class="parameter-pane-title">Group Radar Description</h3>
                            <p class="parameter-pane-desc">Kelola kode dan deskripsi radar group yang dipakai pada section group radar dan fallback quant profile.</p>
                        </div>
                        <div class="parameter-pane-badge">{{ $radarGroups->count() }} groups</div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('parameters.radar-groups.store') }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label">Radar Code</label>
                            <input type="text" name="code" class="form-control" maxlength="10" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Add Radar Group</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Quant Profile</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($radarGroups as $group)
                                    <tr>
                                        <td>{{ $group->code }}</td>
                                        <td>{{ $group->description }}</td>
                                        <td>{{ $group->quantProfile ? 'Available' : 'Not set' }}</td>
                                        <td class="text-end">
                                            <details>
                                                <summary class="btn btn-outline-primary btn-sm">Edit</summary>
                                                <form method="POST" action="{{ route('parameters.radar-groups.update', $group->id) }}" class="mt-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="row g-2 justify-content-end">
                                                        <div class="col-md-2">
                                                            <input type="text" name="code" value="{{ $group->code }}" class="form-control" maxlength="10" required>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <input type="text" name="description" value="{{ $group->description }}" class="form-control" maxlength="255" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted">No radar groups yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.querySelector('[data-parameter-management]');
        if (!wrapper) {
            return;
        }

        const forms = wrapper.querySelectorAll('form');
        const tabTriggers = wrapper.querySelectorAll('[data-parameter-tab-target]');
        const defaultTab = wrapper.getAttribute('data-parameter-active-tab') || 'risk-category';

        function syncHiddenTabInputs(tabKey) {
            forms.forEach(function (form) {
                let input = form.querySelector('input[name="tab"]');

                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tab';
                    form.appendChild(input);
                }

                input.value = tabKey;
            });
        }

        function syncUrl(tabKey) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabKey);
            window.history.replaceState({}, '', url.toString());
        }

        syncHiddenTabInputs(defaultTab);

        tabTriggers.forEach(function (trigger) {
            trigger.addEventListener('shown.bs.tab', function (event) {
                const tabKey = event.target.getAttribute('data-parameter-tab-target');
                if (!tabKey) {
                    return;
                }

                wrapper.setAttribute('data-parameter-active-tab', tabKey);
                syncHiddenTabInputs(tabKey);
                syncUrl(tabKey);
            });
        });
    });
</script>
@endpush
