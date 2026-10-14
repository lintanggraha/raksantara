{{-- Shared create/edit form. Expects $riskAppetite, $metricOptions, $categories. --}}
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Pernyataan appetite</h3></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label required" for="name">Nama</label>
                <input type="text" name="name" id="name" class="form-control" maxlength="255" required
                       value="{{ old('name', $riskAppetite->name) }}" placeholder="Contoh: Appetite risiko operasional TI">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="risk_category_id">Berlaku untuk kategori</label>
                <select name="risk_category_id" id="risk_category_id" class="form-select">
                    <option value="">Semua kategori (default)</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('risk_category_id', $riskAppetite->risk_category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="form-hint">Appetite kategori menggantikan default untuk risk di kategori itu. Satu kategori hanya boleh punya satu appetite aktif.</div>
            </div>
            <div class="col-12">
                <label class="form-label required" for="appetite_statement">Pernyataan</label>
                <textarea name="appetite_statement" id="appetite_statement" rows="3" class="form-control" required
                          placeholder="Contoh: Perusahaan tidak menoleransi gangguan layanan core banking lebih dari 4 jam dan menerima risiko operasional sedang demi kecepatan inovasi produk digital.">{{ old('appetite_statement', $riskAppetite->appetite_statement) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <div>
            <h3 class="card-title">Batas yang dipantau</h3>
            <div class="text-muted small">Dibandingkan otomatis dengan analisis residual setiap risk dalam cakupan.</div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label required" for="threshold_metric">Metrik</label>
                <select name="threshold_metric" id="threshold_metric" class="form-select no-search-select" required data-metric>
                    @foreach($metricOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('threshold_metric', $riskAppetite->threshold_metric) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3" data-quantitative>
                <label class="form-label" for="appetite_limit_value">Target appetite</label>
                <input type="number" step="0.01" min="0" name="appetite_limit_value" id="appetite_limit_value" class="form-control"
                       value="{{ old('appetite_limit_value', $riskAppetite->appetite_limit_value) }}">
                <div class="form-hint">Di atas nilai ini: peringatan.</div>
            </div>
            <div class="col-md-3" data-quantitative>
                <label class="form-label" for="tolerance_limit_value">Batas toleransi</label>
                <input type="number" step="0.01" min="0" name="tolerance_limit_value" id="tolerance_limit_value" class="form-control"
                       value="{{ old('tolerance_limit_value', $riskAppetite->tolerance_limit_value) }}">
                <div class="form-hint">Di atas nilai ini: pelanggaran.</div>
            </div>
            <div class="col-md-2" data-loss-only>
                <label class="form-label" for="unit">Satuan</label>
                <input type="text" name="unit" id="unit" class="form-control" maxlength="100" placeholder="IDR"
                       value="{{ old('unit', $riskAppetite->unit !== 'Skor' ? $riskAppetite->unit : '') }}">
            </div>
        </div>
        <div class="small text-muted mt-2" data-score-hint>Skor residual memakai skala matriks 1–25. Contoh: target 6, toleransi 12.</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <div>
            <h3 class="card-title">Pengesahan & review</h3>
            <div class="text-muted small">Risk appetite disahkan Direksi/Dewan Komisaris dan ditinjau minimal setahun sekali.</div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label" for="approval_reference">Nomor keputusan pengesahan</label>
                <input type="text" name="approval_reference" id="approval_reference" class="form-control" maxlength="255"
                       value="{{ old('approval_reference', $riskAppetite->approval_reference) }}" placeholder="Contoh: SK Direksi No. 012/DIR/2026">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="approved_at">Tanggal pengesahan</label>
                <input type="date" name="approved_at" id="approved_at" class="form-control" max="{{ now()->format('Y-m-d') }}"
                       value="{{ old('approved_at', optional($riskAppetite->approved_at)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="next_review_at">Review berikutnya</label>
                <input type="date" name="next_review_at" id="next_review_at" class="form-control"
                       value="{{ old('next_review_at', optional($riskAppetite->next_review_at)->format('Y-m-d') ?? now()->addYear()->format('Y-m-d')) }}">
            </div>
            <div class="col-12">
                <label class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $riskAppetite->is_active ?? true))>
                    <span class="form-check-label">Aktif: dipakai untuk mengevaluasi risk</span>
                </label>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const metric = document.querySelector('[data-metric]');
    if (!metric) return;
    const toggle = () => {
        const value = metric.value;
        document.querySelectorAll('[data-quantitative]').forEach((el) => { el.hidden = value === 'qualitative_only'; });
        document.querySelectorAll('[data-loss-only]').forEach((el) => { el.hidden = value !== 'residual_potential_loss'; });
        document.querySelectorAll('[data-score-hint]').forEach((el) => { el.hidden = value !== 'residual_risk_score'; });
    };
    metric.addEventListener('change', toggle);
    toggle();
})();
</script>
