@extends('layouts.tabler')

@php
    $isAnalytical = ($dashboardView ?? 'overview') === 'analytical';
    $filterQuery = $dashboardFilters->toQuery();
@endphp

@section('title', $isAnalytical ? 'Analisis Risiko' : 'Dashboard Risiko')

@section('css')
@include('risks.partials.overview-dashboard-styles')
@endsection

@section('content')
<div class="grc-dashboard" data-dashboard-view="{{ $dashboardView }}">
    <div class="grc-toolbar">
        <div class="grc-toolbar-left">
            <nav class="grc-tabs" aria-label="Tampilan dashboard">
                <a href="{{ route('dashboard', array_merge($filterQuery, ['view' => 'overview'])) }}"
                   class="grc-tab {{ $isAnalytical ? '' : 'active' }}" @if(!$isAnalytical) aria-current="page" @endif>
                    Ringkasan
                </a>
                <a href="{{ route('dashboard', array_merge($filterQuery, ['view' => 'analytical'])) }}"
                   class="grc-tab {{ $isAnalytical ? 'active' : '' }}" @if($isAnalytical) aria-current="page" @endif>
                    Analisis
                </a>
            </nav>
            <span class="grc-asof" data-asof hidden></span>
        </div>

        <div class="grc-toolbar-right">
            <form method="GET" action="{{ route('dashboard') }}" class="grc-filters" id="dashboardFilterForm">
                <input type="hidden" name="view" value="{{ $dashboardView }}">
                @unless($isAnalytical)
                    <label class="visually-hidden" for="filterOwner">Unit</label>
                    <select name="owner" id="filterOwner" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua unit</option>
                        @foreach($filterOptions['owners'] as $owner)
                            <option value="{{ $owner }}" @selected($dashboardFilters->owner === $owner)>{{ $owner }}</option>
                        @endforeach
                    </select>
                @endunless
                <label class="visually-hidden" for="filterCategory">Kategori</label>
                <select name="category" id="filterCategory" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua kategori</option>
                    @foreach($filterOptions['categories'] as $category)
                        <option value="{{ $category->id }}" @selected($dashboardFilters->categoryId === (int) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @unless($isAnalytical)
                    <label class="visually-hidden" for="filterPeriod">Periode</label>
                    <select name="period" id="filterPeriod" class="form-select form-select-sm no-search-select" onchange="this.form.submit()">
                        <option value="month" @selected($dashboardFilters->period === 'month')>Bulan ini vs bulan lalu</option>
                        <option value="quarter" @selected($dashboardFilters->period === 'quarter')>Kuartal ini vs kuartal lalu</option>
                    </select>
                @endunless
                @if($dashboardFilters->owner || $dashboardFilters->categoryId)
                    <a href="{{ route('dashboard', ['view' => $dashboardView, 'period' => $dashboardFilters->period]) }}" class="btn btn-sm btn-ghost-secondary">Hapus filter</a>
                @endif
            </form>

            @include('reports.export-buttons', [
                'scope' => 'monitoring',
                'scopeLabel' => 'Laporan Risiko',
            ])

            @if(auth()->check() && !auth()->user()->isSuperadmin())
                <a href="{{ route('risks.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Risk
                </a>
            @endif
        </div>
    </div>

    @if($isAnalytical)
        @include('risks.partials.analytical-dashboard', [
            'dashboardSummary' => $dashboardSummary,
            'levelMigration' => $levelMigration,
        ])
    @else
        @include('risks.partials.overview-dashboard')
    @endif
</div>
@endsection

@section('js')
@include('risks.partials.dashboard-tooltip')
@unless($isAnalytical)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    @include('risks.partials.overview-dashboard-scripts', ['filterQuery' => $filterQuery])
@endunless
@endsection
