@extends('layouts.tabler')

@section('title', 'Tambah Risk Appetite')

@section('content')
<div class="container-xl">
    <div class="mb-3">
        <div class="text-muted small"><a href="{{ route('risk-appetites.index') }}">Risk Appetite</a> / Tambah</div>
        <h2 class="page-title">Tambah risk appetite</h2>
    </div>
    <form method="POST" action="{{ route('risk-appetites.store') }}">
        @csrf
        @include('risk-appetites._form')
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan appetite</button>
            <a href="{{ route('risk-appetites.index') }}" class="btn btn-link">Batal</a>
        </div>
    </form>
</div>
@endsection
