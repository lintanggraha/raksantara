@extends('layouts.tabler')

@section('title', 'Assessment Bank Sumut - Monitoring Assessment')

@section('content_header')
<div class="row align-items-center">
    <div class="col">
        <div class="page-pretitle text-muted text-uppercase fw-bold">
            <i class="fas fa-clipboard-check me-1 text-primary"></i> Monitoring Assessment
        </div>
        <h2 class="page-title d-flex align-items-center gap-2">
            <span>Bank Sumut</span>
            <span class="badge bg-secondary-lt fs-6 fw-semibold">Assessment</span>
        </h2>
        <div class="text-muted mt-1 small">
            Monitoring data &amp; log investigasi assessment Bank Sumut.
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-xl px-0">
    <div class="card shadow-sm border-0" style="min-height: 420px;">
        <div class="card-body d-flex flex-column align-items-center justify-content-center text-muted py-5 my-5">
            <span class="avatar avatar-xl bg-light text-secondary rounded-circle mb-3 shadow-none">
                <i class="far fa-file-alt fa-2x opacity-50"></i>
            </span>
            <h3 class="fw-bold text-dark mb-1">Assessment Bank Sumut</h3>
            <p class="text-secondary small mb-3">Halaman ini belum memiliki data checklist / masih dalam tahap persiapan.</p>
            <div class="d-flex gap-2">
                <a href="{{ route('sulteng.dashboard') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-left me-1"></i> Buka Assessment Sulteng
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
