@extends('layouts.tabler')

@section('title', $riskAppetite->name . ' · Risk Appetite')

@section('content')
<div class="container-xl">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <div class="text-muted small"><a href="{{ route('risk-appetites.index') }}">Risk Appetite</a></div>
            <h2 class="page-title">{{ $riskAppetite->name }}</h2>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <span class="badge {{ $riskAppetite->risk_category_id ? 'bg-blue-lt' : 'bg-purple-lt' }}">{{ $riskAppetite->scopeLabel() }}</span>
                @unless($riskAppetite->is_active)<span class="badge bg-secondary-lt">Nonaktif</span>@endunless
                @if($riskAppetite->isApproved())
                    <span class="badge bg-green-lt">Disahkan {{ $riskAppetite->approved_at->format('d M Y') }}</span>
                @else
                    <span class="badge bg-yellow-lt">Belum disahkan</span>
                @endif
                @if($riskAppetite->isReviewOverdue())<span class="badge bg-red-lt">Review terlambat</span>@endif
            </div>
        </div>
        @if($canManage)
            <a href="{{ route('risk-appetites.edit', $riskAppetite) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i> Ubah</a>
        @endif
    </div>

    @foreach(['success' => 'alert-success', 'error' => 'alert-danger'] as $key => $class)
        @if(session($key))<div class="alert {{ $class }}">{{ session($key) }}</div>@endif
    @endforeach

    @php
        $statusBadges = [
            'breach' => ['bg-red-lt', 'Melewati toleransi'],
            'warning' => ['bg-orange-lt', 'Di atas target'],
            'within' => ['bg-green-lt', 'Dalam appetite'],
            'qualitative' => ['bg-secondary-lt', 'Kualitatif'],
            'unconfigured' => ['bg-secondary-lt', 'Belum ada residual'],
        ];
        $counts = $risks->countBy('appetite_status');
        $format = fn ($value) => $value === null ? '-' : rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    @endphp

    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="subheader mb-1">Pernyataan</div>
                    <p style="white-space: pre-line;">{{ $riskAppetite->appetite_statement }}</p>
                    <div class="subheader mb-1">Batas</div>
                    @if($riskAppetite->isQuantitative())
                        <div class="mb-2">{{ $riskAppetite->threshold_metric_label }}</div>
                        <div class="d-flex gap-4">
                            <div><div class="text-muted small">Target</div><div class="h3 mb-0">{{ $format($riskAppetite->appetite_limit_value) }}</div></div>
                            <div><div class="text-muted small">Toleransi</div><div class="h3 mb-0 text-danger">{{ $format($riskAppetite->tolerance_limit_value) }}</div></div>
                            <div><div class="text-muted small">Satuan</div><div class="h3 mb-0">{{ $riskAppetite->unit }}</div></div>
                        </div>
                    @else
                        <div class="text-muted">Kualitatif: tidak dibandingkan otomatis dengan residual risk.</div>
                    @endif
                    <hr>
                    <div class="small">
                        <div><span class="text-muted">Pengesahan:</span> {{ $riskAppetite->approval_reference ?: '-' }}</div>
                        <div><span class="text-muted">Review berikutnya:</span> {{ optional($riskAppetite->next_review_at)->format('d M Y') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Risk dalam cakupan</h3>
                        <div class="text-muted small">
                            {{ $risks->count() }} risk aktif dalam akses Anda
                            · {{ $counts['breach'] ?? 0 }} melewati toleransi · {{ $counts['warning'] ?? 0 }} di atas target
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead><tr><th>Risk</th><th>Residual</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($risks as $risk)
                                @php
                                    [$badgeClass, $badgeLabel] = $statusBadges[$risk->appetite_status] ?? $statusBadges['unconfigured'];
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('risks.show', $risk->id) }}">{{ $risk->risk_id }}</a>
                                        @if((int) $risk->risk_appetite_id === (int) $riskAppetite->id)<span class="badge bg-secondary-lt ms-1">override</span>@endif
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($risk->risk_title, 90) }} · {{ \App\Support\RiskOwnerAccess::displayLabel($risk->risk_owner) }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if($riskAppetite->effectiveThresholdMetric() === \App\RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS)
                                            {{ $risk->potential_loss !== null ? 'Rp ' . number_format($risk->potential_loss, 0, ',', '.') : '-' }}
                                        @else
                                            {{ $risk->residual_risk_score ?? '-' }}
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada risk aktif dalam cakupan appetite ini yang terlihat oleh Anda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
