@extends('layouts.tabler')

@section('title', 'Add Third-Party Vendor')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="mb-0">Add Vendor</h1>
    <a href="{{ route('vendors.index') }}" class="btn btn-secondary btn-sm with-icon">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>
@stop

@section('content')
<div class="card shadow" style="max-width: 800px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('vendors.store') }}" method="POST">
            @csrf
            
            @include('vendors.form')

            <div class="mt-4 text-end">
                <a href="{{ route('vendors.index') }}" class="btn btn-link">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Vendor</button>
            </div>
        </form>
    </div>
</div>
@stop
