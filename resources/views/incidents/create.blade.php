@extends('layouts.tabler')

@section('title', 'Catat Insiden / Temuan Baru')

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
        <h2 class="page-title mb-1">Catat Insiden / Temuan Baru</h2>
        <div class="text-muted">Formulir pencatatan insiden operasional terhubung dengan risk register atau temuan baru mandiri.</div>
    </div>
    <div>
        <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard Insiden
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
            <h3 class="card-title mb-0">Formulir Pencatatan Insiden &amp; Loss Event</h3>
            <div class="text-muted small">Insiden tanpa kaitan risk akan dicatat sebagai <strong>Temuan Baru</strong> dan dapat dipromosikan ke Risk Register.</div>
        </div>
        <span class="badge bg-purple-lt">
            <i class="fas fa-shield-alt me-1"></i> Operasional &amp; Financial Loss
        </span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('incidents.store') }}">
            @csrf

            {{-- SECTION 1: Kaitan Risiko, Client Terdampak & Divisi Pemilik --}}
            <div class="form-section-title">
                <i class="fas fa-link text-primary"></i> 1. Kaitan Risiko, Client Terdampak &amp; Divisi Pemilik
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Related Risk (Opsional)</label>
                    <select name="risk_id" id="create_risk_id" class="form-select">
                        <option value="">-- Tanpa Kaitan Risk (Temuan Baru) --</option>
                        @foreach($riskOptions as $riskOption)
                            <option 
                                value="{{ $riskOption->id }}" 
                                data-owner="{{ $riskOption->risk_owner }}"
                                @if((string) old('risk_id', $selectedRiskId) === (string) $riskOption->id) selected @endif
                            >
                                [{{ $riskOption->risk_id }}] {{ $riskOption->risk_title }} ({{ $riskOption->risk_owner }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">
                        Pilih item risk jika insiden terkait risiko yang sudah ada, atau biarkan kosong jika merupakan <strong>temuan baru</strong>.
                    </small>
                </div>

                <div class="col-md-6">
                    <label class="form-label incident-form-label">Risk Owner / Divisi Pemilik</label>
                    <select name="risk_owner" id="create_risk_owner" class="form-select">
                        <option value="">Pilih Divisi / Unit</option>
                        @foreach($riskOwnerOptions as $ownerOption)
                            <option value="{{ $ownerOption }}" @if(old('risk_owner') === $ownerOption) selected @endif>
                                {{ $ownerOption }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">Divisi Collega yang bertanggung jawab terhadap insiden/temuan ini.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label incident-form-label">Client Terdampak (Bank / Lembaga)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-building text-muted"></i></span>
                        <input 
                            type="text" 
                            name="affected_client" 
                            id="create_affected_client" 
                            class="form-control" 
                            list="collega_client_list" 
                            placeholder="Pilih atau ketik nama client (contoh: BANK SULTENG)"
                            value="{{ old('affected_client') }}"
                            maxlength="150"
                        >
                    </div>
                    <datalist id="collega_client_list">
                        @foreach($clientOptions as $cOpt)
                            <option value="{{ $cOpt }}">{{ $cOpt }}</option>
                        @endforeach
                    </datalist>
                    <small class="text-muted d-block mt-1">
                        Pilih bank client Collega dari daftar atau ketik nama client/mitra terdampak.
                    </small>
                </div>

                <div class="col-md-6">
                    <label class="form-label incident-form-label">Estimasi Kerugian Finansial (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text fw-bold">Rp</span>
                        <input 
                            type="text" 
                            name="loss_amount" 
                            id="create_loss_amount"
                            class="form-control js-currency-input currency-tag" 
                            placeholder="0" 
                            value="{{ old('loss_amount') }}"
                        >
                    </div>
                    <small class="text-muted d-block mt-1">Nominal kerugian finansial yang timbul (diakumulasi pada rekap bulanan &amp; tahunan).</small>
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
                        value="{{ old('title') }}" 
                        required 
                        placeholder="Contoh: Gangguan API Pembayaran / Penemuan Celana Keamanan Server"
                    >
                </div>
                <div class="col-md-3">
                    <label class="form-label incident-form-label">Waktu Kejadian (Occurred At) <span class="text-danger">*</span></label>
                    <input 
                        type="datetime-local" 
                        name="occurred_at" 
                        class="form-control" 
                        value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" 
                        required
                    >
                </div>
                <div class="col-md-3">
                    <label class="form-label incident-form-label">Waktu Terdeteksi (Detected At)</label>
                    <input 
                        type="datetime-local" 
                        name="detected_at" 
                        class="form-control" 
                        value="{{ old('detected_at') }}"
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label incident-form-label">Tingkat Keparahan (Severity) <span class="text-danger">*</span></label>
                    <select name="severity" class="form-select" required>
                        @foreach($severityOptions as $option)
                            <option value="{{ $option }}" @if(old('severity', 'Medium') === $option) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label incident-form-label">Status Penanganan <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        @foreach($statusOptions as $option)
                            <option value="{{ $option }}" @if(old('status', 'Open') === $option) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label incident-form-label">Efektivitas Mitigasi (Mitigation Verdict) <span class="text-danger">*</span></label>
                    <select name="mitigation_verdict" class="form-select" required>
                        @foreach($mitigationVerdictOptions as $option)
                            <option value="{{ $option }}" @if(old('mitigation_verdict', 'Not Tested') === $option) selected @endif>{{ $option }}</option>
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
                        placeholder="Jelaskan secara kronologis kejadian atau detail temuan yang diidentifikasi..."
                    >{{ old('incident_description') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Dampak Bisnis &amp; Operasional (Business Impact)</label>
                    <textarea 
                        name="business_impact" 
                        class="form-control" 
                        rows="3" 
                        maxlength="5000" 
                        placeholder="Dampak pada nasabah, layanan operasional, regulasi, atau reputasi..."
                    >{{ old('business_impact') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Tindakan Cepat (Immediate Action)</label>
                    <textarea 
                        name="immediate_action" 
                        class="form-control" 
                        rows="3" 
                        maxlength="5000" 
                        placeholder="Langkah penanganan darurat atau isolasi masalah yang telah diambil..."
                    >{{ old('immediate_action') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Akar Penyebab (Root Cause)</label>
                    <textarea 
                        name="root_cause" 
                        class="form-control" 
                        rows="3" 
                        maxlength="5000" 
                        placeholder="Analisis penyebab utama (misal: human error, kegagalan infrastruktur, vendor failure)..."
                    >{{ old('root_cause') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label incident-form-label">Rencana Perbaikan Jangka Panjang (Corrective Action / CAPA)</label>
                    <textarea 
                        name="corrective_action" 
                        class="form-control" 
                        rows="3" 
                        maxlength="5000" 
                        placeholder="Rencana perbaikan agar insiden serupa tidak terulang kembali..."
                    >{{ old('corrective_action') }}</textarea>
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
                        placeholder="Catatan hasil investigasi akhir atau status penyelesaian..."
                    >{{ old('resolution_notes') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label incident-form-label">Waktu Selesai / Ditutup (Closed At)</label>
                    <input 
                        type="datetime-local" 
                        name="closed_at" 
                        class="form-control" 
                        value="{{ old('closed_at') }}"
                    >
                    <small class="text-muted d-block mt-1">Kosongkan jika insiden masih dalam proses investigasi/mitigasi.</small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('incidents.index') }}" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary with-icon">
                    <i class="fas fa-plus-circle me-1"></i> Simpan Insiden / Temuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        // Sync Risk Owner when Risk Option changes
        const riskSelect = document.getElementById('create_risk_id');
        const ownerSelect = document.getElementById('create_risk_owner');

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

        const lossInput = document.getElementById('create_loss_amount');
        if (lossInput) {
            if (lossInput.value) {
                lossInput.value = formatCurrencyDisplay(lossInput.value);
            }
            lossInput.addEventListener('blur', function () {
                if (this.value) {
                    this.value = formatCurrencyDisplay(this.value);
                }
            });
        }
    })();
</script>
@endsection
