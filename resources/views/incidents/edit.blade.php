@extends('layouts.tabler')

@section('title', 'Edit Insiden - ' . $incident->incident_code)

@section('css')
<style>
    .incident-form-card {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
    }

    .incident-form-card .card-header {
        border-bottom: 1px solid #e5edf6;
        background: #f8fbff;
    }

    .form-section-title {
        font-size: 0.85rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 700;
        color: #1e40af;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.4rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .incident-form-label {
        font-size: 0.78rem;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        font-weight: 700;
        color: #475569;
    }

    .currency-tag {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 700;
    }
</style>
@endsection

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="page-title mb-1">Edit Insiden: {{ $incident->incident_code }}</h2>
        <div class="text-muted">Perbarui data insiden, status investigasi, atau estimasi kerugian finansial.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('incidents.learning.show', $incident->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-graduation-cap me-1"></i> Learning &amp; CAPA
        </a>
        <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

@section('content')
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-1"></i>
        <strong>Periksa kembali input Anda:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card incident-form-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="card-title mb-0">Formulir Pembaruan Data Insiden</h3>
            <div class="text-muted small">
                Dilaporkan oleh: <strong>{{ optional($incident->reporter)->name ?? 'Sistem' }}</strong> 
                | Terakhir diperbarui: <strong>{{ optional($incident->updated_at)->format('d M Y H:i') ?? '-' }}</strong>
            </div>
        </div>
        <div>
            @if($incident->risk)
                <span class="badge bg-blue-lt">
                    <i class="fas fa-link me-1"></i> Terhubung: {{ $incident->risk->risk_id }}
                </span>
            @else
                <span class="badge bg-purple-lt">
                    <i class="fas fa-sparkles me-1"></i> Temuan Baru (Unlinked)
                </span>
            @endif
        </div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('incidents.update', $incident->id) }}">
            @csrf
            @method('PUT')

            {{-- SECTION 1: Kaitan Risiko, Client Terdampak & Divisi Pemilik --}}
            <div class="form-section-title">
                <i class="fas fa-link text-primary"></i> 1. Kaitan Risiko, Client Terdampak &amp; Divisi Pemilik
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Related Risk (Opsional)</label>
                    <select name="risk_id" id="edit_risk_id" class="form-select">
                        <option value="">-- Tanpa Kaitan Risk (Temuan Baru) --</option>
                        @foreach($riskOptions as $riskOption)
                            <option 
                                value="{{ $riskOption->id }}" 
                                data-owner="{{ $riskOption->risk_owner }}"
                                @if((string) old('risk_id', $incident->risk_id) === (string) $riskOption->id) selected @endif
                            >
                                [{{ $riskOption->risk_id }}] {{ $riskOption->risk_title }} ({{ $riskOption->risk_owner }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">
                        Dapat dihubungkan ke risk yang ada atau dibiarkan kosong sebagai temuan mandiri.
                    </small>
                </div>

                <div class="col-md-6">
                    <label class="form-label incident-form-label">Risk Owner / Divisi Pemilik</label>
                    <select name="risk_owner" id="edit_risk_owner" class="form-select">
                        <option value="">Pilih Divisi / Unit</option>
                        @foreach($riskOwnerOptions as $ownerOption)
                            <option value="{{ $ownerOption }}" @if(old('risk_owner', $incident->risk_owner) === $ownerOption) selected @endif>
                                {{ $ownerOption }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">Divisi atau unit yang bertanggung jawab terhadap insiden ini.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label incident-form-label">Client Terdampak (Bank / Lembaga)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-building text-muted"></i></span>
                        <input 
                            type="text" 
                            name="affected_client" 
                            id="edit_affected_client" 
                            class="form-control" 
                            list="affected_party_list" 
                            placeholder="Pilih atau ketik nama client (contoh: BANK CONTOH A)"
                            value="{{ old('affected_client', $incident->affected_client) }}"
                            maxlength="150"
                        >
                    </div>
                    <datalist id="affected_party_list">
                        @foreach($clientOptions as $cOpt)
                            <option value="{{ $cOpt }}">{{ $cOpt }}</option>
                        @endforeach
                    </datalist>
                    <small class="text-muted d-block mt-1">
                        Pilih client atau pihak terdampak dari daftar atau perbarui nama client/mitra terdampak.
                    </small>
                </div>

                <div class="col-md-6">
                    <label class="form-label incident-form-label">Estimasi Kerugian Finansial (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text fw-bold">Rp</span>
                        <input 
                            type="text" 
                            name="loss_amount" 
                            id="edit_loss_amount"
                            class="form-control js-currency-input currency-tag" 
                            placeholder="0" 
                            value="{{ old('loss_amount', $incident->loss_amount ? number_format($incident->loss_amount, 0, ',', '.') : '') }}"
                        >
                    </div>
                    <small class="text-muted d-block mt-1">Nominal kerugian finansial yang timbul.</small>
                </div>
            </div>

            {{-- SECTION 2: Klasifikasi & Waktu Kejadian --}}
            <div class="form-section-title">
                <i class="fas fa-calendar-alt text-primary"></i> 2. Klasifikasi &amp; Waktu Kejadian
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Judul Insiden / Temuan <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="title" 
                        class="form-control" 
                        maxlength="255" 
                        value="{{ old('title', $incident->title) }}" 
                        required
                    >
                </div>
                <div class="col-md-3">
                    <label class="form-label incident-form-label">Waktu Kejadian (Occurred At) <span class="text-danger">*</span></label>
                    <input 
                        type="datetime-local" 
                        name="occurred_at" 
                        class="form-control" 
                        value="{{ old('occurred_at', optional($incident->occurred_at)->format('Y-m-d\TH:i')) }}" 
                        required
                    >
                </div>
                <div class="col-md-3">
                    <label class="form-label incident-form-label">Waktu Terdeteksi (Detected At)</label>
                    <input 
                        type="datetime-local" 
                        name="detected_at" 
                        class="form-control" 
                        value="{{ old('detected_at', optional($incident->detected_at)->format('Y-m-d\TH:i')) }}"
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label incident-form-label">Tingkat Keparahan (Severity) <span class="text-danger">*</span></label>
                    <select name="severity" class="form-select" required>
                        @foreach($severityOptions as $option)
                            <option value="{{ $option }}" @if(old('severity', $incident->severity) === $option) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label incident-form-label">Status Penanganan <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        @foreach($statusOptions as $option)
                            <option value="{{ $option }}" @if(old('status', $incident->status) === $option) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label incident-form-label">Efektivitas Mitigasi (Mitigation Verdict) <span class="text-danger">*</span></label>
                    <select name="mitigation_verdict" class="form-select" required>
                        @foreach($mitigationVerdictOptions as $option)
                            <option value="{{ $option }}" @if(old('mitigation_verdict', $incident->mitigation_verdict) === $option) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- SECTION 3: Rincian Insiden & Analisis Akar Masalah --}}
            <div class="form-section-title">
                <i class="fas fa-file-alt text-primary"></i> 3. Rincian Insiden &amp; Analisis Dampak
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label incident-form-label">Deskripsi Lengkap Insiden / Detail Temuan <span class="text-danger">*</span></label>
                    <textarea 
                        name="incident_description" 
                        class="form-control" 
                        rows="4" 
                        maxlength="10000" 
                        required
                    >{{ old('incident_description', $incident->incident_description) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Dampak Bisnis &amp; Operasional (Business Impact)</label>
                    <textarea 
                        name="business_impact" 
                        class="form-control" 
                        rows="3" 
                        maxlength="5000"
                    >{{ old('business_impact', $incident->business_impact) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Tindakan Cepat (Immediate Action)</label>
                    <textarea 
                        name="immediate_action" 
                        class="form-control" 
                        rows="3" 
                        maxlength="5000"
                    >{{ old('immediate_action', $incident->immediate_action) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Akar Penyebab (Root Cause)</label>
                    <textarea 
                        name="root_cause" 
                        class="form-control" 
                        rows="3" 
                        maxlength="5000"
                    >{{ old('root_cause', $incident->root_cause) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Rencana Perbaikan Jangka Panjang (Corrective Action / CAPA)</label>
                    <textarea 
                        name="corrective_action" 
                        class="form-control" 
                        rows="3" 
                        maxlength="5000"
                    >{{ old('corrective_action', $incident->corrective_action) }}</textarea>
                </div>
            </div>

            {{-- SECTION 4: Penyelesaian & Penutupan --}}
            <div class="form-section-title">
                <i class="fas fa-check-circle text-primary"></i> 4. Catatan Penyelesaian &amp; Penutupan
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label incident-form-label">Catatan Resolusi (Resolution Notes)</label>
                    <textarea 
                        name="resolution_notes" 
                        class="form-control" 
                        rows="2" 
                        maxlength="5000"
                    >{{ old('resolution_notes', $incident->resolution_notes) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label incident-form-label">Waktu Selesai / Ditutup (Closed At)</label>
                    <input 
                        type="datetime-local" 
                        name="closed_at" 
                        class="form-control" 
                        value="{{ old('closed_at', optional($incident->closed_at)->format('Y-m-d\TH:i')) }}"
                    >
                    <small class="text-muted d-block mt-1">Kosongkan jika insiden belum selesai.</small>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                @if(!$incident->risk_id)
                    <a 
                        href="{{ route('risks.create', ['from_incident' => $incident->id]) }}" 
                        class="btn btn-outline-success with-icon"
                    >
                        <i class="fas fa-plus-circle me-1"></i> Promosikan Jadi Risk Baru
                    </a>
                @else
                    <div></div>
                @endif

                <div class="d-flex gap-2">
                    <a href="{{ route('incidents.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary with-icon">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        // Sync Risk Owner when Risk Option changes
        const riskSelect = document.getElementById('edit_risk_id');
        const ownerSelect = document.getElementById('edit_risk_owner');

        if (riskSelect && ownerSelect) {
            riskSelect.addEventListener('change', function () {
                const selectedOption = riskSelect.options[riskSelect.selectedIndex];
                const owner = selectedOption ? selectedOption.getAttribute('data-owner') : null;
                if (owner && owner !== '') {
                    ownerSelect.value = owner;
                }
            });
        }

        // Currency input formatting helper
        function formatCurrencyDisplay(val) {
            if (val === null || val === undefined || val === '') return '';
            const num = parseFloat(String(val).replace(/[^0-9.-]+/g, ''));
            if (isNaN(num)) return val;
            return new Intl.NumberFormat('id-ID').format(num);
        }

        const lossInput = document.getElementById('edit_loss_amount');
        if (lossInput) {
            lossInput.addEventListener('blur', function () {
                if (this.value) {
                    this.value = formatCurrencyDisplay(this.value);
                }
            });
        }
    })();
</script>
@endsection
