@extends('layouts.tabler')

@section('title', 'Ubah Risk Appetite')

@section('content')
<div class="container-xl">
    <div class="mb-3">
        <div class="text-muted small"><a href="{{ route('risk-appetites.index') }}">Risk Appetite</a> / <a href="{{ route('risk-appetites.show', $riskAppetite) }}">{{ $riskAppetite->name }}</a></div>
        <h2 class="page-title">Ubah risk appetite</h2>
    </div>

    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form method="POST" action="{{ route('risk-appetites.update', $riskAppetite) }}">
        @csrf @method('PUT')
        @include('risk-appetites._form')
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            <a href="{{ route('risk-appetites.show', $riskAppetite) }}" class="btn btn-link">Batal</a>
        </div>
    </form>

    <div class="card mt-4 border-danger-subtle">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="small text-muted">
                @if($overrideCount > 0)
                    Appetite ini dipakai sebagai override oleh {{ $overrideCount }} risk, sehingga tidak bisa dihapus. Nonaktifkan bila tidak dipakai lagi.
                @else
                    Hapus permanen hanya bila appetite ini dibuat keliru. Untuk appetite lama yang sudah diganti, nonaktifkan saja agar riwayatnya tetap ada.
                @endif
            </div>
            @if($overrideCount === 0)
                <form method="POST" action="{{ route('risk-appetites.destroy', $riskAppetite) }}" onsubmit="return confirm('Hapus appetite ini secara permanen?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
