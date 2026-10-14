@extends('layouts.tabler')

@section('title', 'Risk Appetite')

@section('content')
<div class="container-xl">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h2 class="page-title">Risk Appetite</h2>
            <div class="text-muted mt-1" style="max-width: 72ch;">
                Tingkat risiko yang bersedia diambil organisasi per kategori. <strong>Appetite</strong> adalah target; <strong>toleransi</strong> adalah batas maksimum.
                Appetite berlaku otomatis ke semua risk di kategorinya, dan dibandingkan dengan skor atau potensi kerugian residual setiap risk.
            </div>
        </div>
        @if($canManage)
            <a href="{{ route('risk-appetites.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah appetite
            </a>
        @endif
    </div>

    @foreach(['success' => 'alert-success', 'error' => 'alert-danger'] as $key => $class)
        @if(session($key))<div class="alert {{ $class }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="row row-cards mb-3">
        @foreach([
            ['Risk tercakup appetite', $totals['covered'] . ' / ' . $totals['risks'], 'Risk aktif dalam cakupan akses Anda', $totals['risks'] > 0 && $totals['covered'] < $totals['risks'] ? 'text-warning' : ''],
            ['Melewati toleransi', $totals['breach'], 'Residual di atas batas maksimum', $totals['breach'] > 0 ? 'text-danger' : ''],
            ['Di atas target', $totals['warning'], 'Masih di bawah toleransi', $totals['warning'] > 0 ? 'text-warning' : ''],
            ['Dalam appetite', $totals['within'], 'Dari risk dengan batas angka', ''],
        ] as [$label, $value, $hint, $class])
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm"><div class="card-body">
                    <div class="subheader">{{ $label }}</div>
                    <div class="h1 mb-0 {{ $class }}">{{ $value }}</div>
                    <div class="text-muted small">{{ $hint }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    @if($appetites->isNotEmpty() && !$hasDefault && $uncoveredCategories->isNotEmpty())
        <div class="alert alert-warning">
            <strong>{{ $uncoveredCategories->count() }} kategori risk belum punya appetite:</strong>
            {{ $uncoveredCategories->take(8)->implode(', ') }}{{ $uncoveredCategories->count() > 8 ? ', …' : '' }}.
            Tambahkan appetite per kategori, atau satu appetite <em>default</em> untuk semua kategori.
        </div>
    @endif

    <div class="card">
        @if($appetites->isEmpty())
            <div class="card-body py-5 text-center">
                <h3 class="mb-2">Risk appetite belum ditetapkan</h3>
                @if($canManage)
                    <p class="text-muted mx-auto" style="max-width: 60ch;">
                        Mulailah dengan satu appetite <strong>default</strong> untuk semua kategori, misalnya skor residual target 6 dan toleransi 12.
                        Setelah itu tambahkan appetite khusus untuk kategori yang perlu batas berbeda, misalnya Kepatuhan atau Keamanan Informasi.
                        Catat nomor dan tanggal pengesahan Direksi/Dewan pada setiap appetite.
                    </p>
                    <a href="{{ route('risk-appetites.create') }}" class="btn btn-primary">Tambah appetite default</a>
                @else
                    <p class="text-muted mx-auto mb-0" style="max-width: 60ch;">
                        Risk appetite disusun oleh fungsi manajemen risiko (Compliance) dan disahkan Direksi/Dewan Komisaris.
                        Hubungi tim tersebut bila appetite untuk kategori risk Anda belum tersedia.
                    </p>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table card-table table-vcenter">
                    <thead>
                        <tr>
                            <th>Cakupan</th>
                            <th>Appetite</th>
                            <th>Target / toleransi</th>
                            <th>Risk tercakup</th>
                            <th>Pengesahan & review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appetites as $appetite)
                            @php($coverage = $perAppetite[$appetite->id] ?? ['covered' => 0, 'breach' => 0, 'warning' => 0])
                            <tr class="{{ $appetite->is_active ? '' : 'text-muted' }}">
                                <td>
                                    <span class="badge {{ $appetite->risk_category_id ? 'bg-blue-lt' : 'bg-purple-lt' }}">{{ $appetite->scopeLabel() }}</span>
                                    @unless($appetite->is_active)<span class="badge bg-secondary-lt">Nonaktif</span>@endunless
                                </td>
                                <td>
                                    <a href="{{ route('risk-appetites.show', $appetite) }}" class="fw-medium">{{ $appetite->name }}</a>
                                    <div class="small text-muted text-truncate" style="max-width: 340px;" title="{{ $appetite->appetite_statement }}">{{ $appetite->appetite_statement }}</div>
                                </td>
                                <td class="text-nowrap">
                                    @if($appetite->isQuantitative())
                                        {{ rtrim(rtrim(number_format($appetite->appetite_limit_value, 2, ',', '.'), '0'), ',') }}
                                        / <strong>{{ rtrim(rtrim(number_format($appetite->tolerance_limit_value, 2, ',', '.'), '0'), ',') }}</strong>
                                        <span class="text-muted small">{{ $appetite->unit }}</span>
                                        <div class="small text-muted">{{ $appetite->threshold_metric_label }}</div>
                                    @else
                                        <span class="text-muted">Kualitatif</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $coverage['covered'] }}
                                    @if($coverage['breach'])<span class="badge bg-red-lt ms-1">{{ $coverage['breach'] }} melewati</span>@endif
                                    @if($coverage['warning'])<span class="badge bg-orange-lt ms-1">{{ $coverage['warning'] }} di atas target</span>@endif
                                    @if($appetite->risks_count)<div class="small text-muted">{{ $appetite->risks_count }} lewat override</div>@endif
                                </td>
                                <td class="small">
                                    @if($appetite->isApproved())
                                        <span class="text-success">Disahkan</span> {{ $appetite->approved_at->format('d M Y') }}
                                        <div class="text-muted text-truncate" style="max-width: 200px;">{{ $appetite->approval_reference }}</div>
                                    @else
                                        <span class="badge bg-yellow-lt">Belum disahkan</span>
                                    @endif
                                    @if($appetite->next_review_at)
                                        <div class="{{ $appetite->isReviewOverdue() ? 'text-danger' : 'text-muted' }}">Review {{ $appetite->next_review_at->format('d M Y') }}{{ $appetite->isReviewOverdue() ? ' (terlambat)' : '' }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
