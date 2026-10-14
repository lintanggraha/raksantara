@extends('layouts.tabler')

@section('title', 'Risk Recapitulation')

@section('content_header')
<div class="d-flex align-items-center flex-wrap gap-2">
    <h1 class="mb-0">Risk Recapitulation</h1>
</div>
@stop

@section('content')
@include('risks.partials.recap-content')
@stop


