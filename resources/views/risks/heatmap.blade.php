@extends('layouts.tabler')

@section('title', 'Risk Heat Map')

@section('css')
<style>
.header-toolbar .btn.with-icon {
    display: inline-flex;
    align-items: center;
    gap: 0.38rem;
}

.header-toolbar .btn.with-icon i {
    margin: 0;
    line-height: 1;
}
</style>
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex flex-wrap align-items-center gap-2 header-toolbar">
        @include('reports.export-buttons', [
            'scope' => 'heatmap',
            'scopeLabel' => 'Heat Map',
        ])
    </div>
    <h1 class="mb-0">Risk Heat Map</h1>
</div>
@stop

@section('content')
@include('risks.partials.heatmap-content')
@stop

