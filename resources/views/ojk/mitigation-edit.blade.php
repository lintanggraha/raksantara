@extends('layouts.tabler')

@section('title', 'Edit OJK Mitigation Point')

@section('content_header')
    <h2 class="page-title mb-1">Edit Point Mitigasi OJK</h2>
    <div class="text-muted">Perbarui data point mitigasi lalu simpan untuk mengupdate timeline.</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Point {{ $item->item_number ?: $item->id }}</h3>
        <a href="{{ route('ojk-mitigation.index') }}" class="btn btn-outline-secondary btn-sm with-icon">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('ojk-mitigation.update', $item->id) }}">
            @csrf
            @method('PUT')

            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">No</label>
                    <input type="text" name="item_number" class="form-control form-control-sm" value="{{ old('item_number', $item->item_number) }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label">PIC</label>
                    <input type="text" name="pic" class="form-control form-control-sm" value="{{ old('pic', $item->pic) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Mitigasi</label>
                    <select name="mitigation_status" class="form-select form-select-sm" required>
                        @foreach($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}" {{ old('mitigation_status', $item->mitigation_status) === $statusOption ? 'selected' : '' }}>
                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Action Plan</label>
                <textarea name="action_plan" class="form-control form-control-sm" rows="5" required>{{ old('action_plan', $item->action_plan) }}</textarea>
            </div>

            <div class="mt-3">
                <label class="form-label">Solusi</label>
                <textarea name="solution" class="form-control form-control-sm" rows="4">{{ old('solution', $item->solution) }}</textarea>
            </div>

            <div class="mt-3">
                <label class="form-label">Target</label>
                <textarea name="target" class="form-control form-control-sm" rows="4">{{ old('target', $item->target) }}</textarea>
                <small class="text-muted">Tanggal timeline akan diparse dari teks target (contoh: 10 Maret 2026).</small>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm with-icon">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('ojk-mitigation.index') }}" class="btn btn-outline-secondary btn-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
