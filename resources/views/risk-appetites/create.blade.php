@extends('layouts.tabler')

@section('title', 'Add Corporate Risk Appetite')

@section('content')
<div class="container-xl">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Declare New Corporate Risk Appetite</h3>
                </div>
                <form action="{{ route('risk-appetites.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label required">Appetite Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g., Financial Loss Limits" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Category Filter (Optional)</label>
                                    <input type="text" name="risk_category" class="form-control" placeholder="e.g., Financial, Operational" value="{{ old('risk_category') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label required">Threshold Metric</label>
                                    <select name="threshold_metric" class="form-control" required>
                                        @foreach($metricOptions as $metricValue => $metricLabel)
                                            <option value="{{ $metricValue }}" {{ old('threshold_metric', \App\RiskAppetite::THRESHOLD_METRIC_QUALITATIVE) === $metricValue ? 'selected' : '' }}>
                                                {{ $metricLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-hint">Use qualitative only if this framework is guidance text only. Choose a quantitative metric if it should drive alert thresholds.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label required">Appetite Statement</label>
                            <textarea name="appetite_statement" class="form-control" rows="3" placeholder="Describe the corporate stance. e.g., The company has zero tolerance for non-compliance with central bank regulations..." required>{{ old('appetite_statement') }}</textarea>
                            <small class="form-hint">Qualitative description visible to risk owners for guidance.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Quantitative Target (Appetite)</label>
                                    <input type="number" step="0.01" name="appetite_limit_value" class="form-control" placeholder="e.g., 500000000" value="{{ old('appetite_limit_value') }}">
                                    <small class="form-hint">Required when using a quantitative threshold metric. This is the target ceiling before the risk goes above appetite.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Maximum Tolerance Limit</label>
                                    <input type="number" step="0.01" name="tolerance_limit_value" class="form-control" placeholder="e.g., 1000000000" value="{{ old('tolerance_limit_value') }}">
                                    <small class="form-hint">Required when using a quantitative threshold metric. This is the hard breach boundary.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Unit of Measure</label>
                                    <input type="text" name="unit" class="form-control" placeholder="e.g., IDR, Incidents, Hours" value="{{ old('unit') }}">
                                    <small class="form-hint">Use <strong>Score</strong> for residual risk score, or a currency/unit for loss-based appetite.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <span class="form-check-label">Available for Selection (Active)</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="card-footer text-end">
                        <a href="{{ route('risk-appetites.index') }}" class="btn btn-link">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Declaration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
