@extends('layouts.tabler')

@php
    $isEdit = !is_null($program ?? null);
    $formTitle = $isEdit ? 'Edit Program Kerja' : 'Add Program Kerja';
    $submitLabel = $isEdit ? 'Update Program Kerja' : 'Simpan Program Kerja';
@endphp

@section('title', $formTitle . ' - ' . $sectionConfig['title'])

@section('content_header')
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title mb-1">{{ $sectionConfig['title'] }}</h2>
            <div class="text-muted">{{ $formTitle }}</div>
        </div>
    </div>
@endsection

@section('content')
    <style>
        .program-form-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .program-form-title {
            margin: 0;
            font-size: 1.06rem;
            font-weight: 700;
            color: #0f172a;
        }
    </style>

    <div class="card program-form-card shadow-sm">
        <div class="card-header">
            <h3 class="program-form-title">{{ $formTitle }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ $isEdit ? route($sectionConfig['route_base'] . '.update', $program->id) : route($sectionConfig['route_base'] . '.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">ID (Auto Generated)</label>
                            <input type="text" class="form-control" value="{{ $nextWorkId }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Program / Key Initiative <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="program_key_initiative"
                        class="form-control"
                        value="{{ old('program_key_initiative', optional($program)->program_kerja) }}"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Description & Rationales <span class="text-danger">*</span></label>
                    <textarea
                        name="description_rationales"
                        class="form-control"
                        rows="4"
                        maxlength="3000"
                        required
                    >{{ old('description_rationales', optional($program)->deskripsi) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deliverables <span class="text-danger">*</span></label>
                    <textarea
                        name="deliverables"
                        class="form-control"
                        rows="4"
                        maxlength="3000"
                        required
                    >{{ old('deliverables', optional($program)->target_sasaran_kerja) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">How to Measure <span class="text-danger">*</span></label>
                    <textarea
                        name="how_to_measure"
                        class="form-control"
                        rows="4"
                        maxlength="3000"
                        required
                    >{{ old('how_to_measure', optional($program)->alignment_champion_program) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Budget <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="budget"
                        class="form-control"
                        maxlength="255"
                        value="{{ old('budget', optional($program)->alignment_risk_profile_2025) }}"
                        placeholder="Contoh: 120000000 atau 120.000.000"
                        required
                    >
                    <small class="text-muted">Hanya angka dan tanda baca (.,!).</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Suport Needed</label>
                    <textarea
                        name="support_needed"
                        class="form-control"
                        rows="4"
                        maxlength="3000"
                    >{{ old('support_needed', optional($program)->support_needed) }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route($sectionConfig['route_base']) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> {{ $submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
