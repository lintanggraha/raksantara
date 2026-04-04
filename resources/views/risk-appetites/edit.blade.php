@extends('layouts.tabler')

@section('title', 'Edit Corporate Risk Appetite')

@section('content')
<div class="container-xl">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Corporate Risk Appetite</h3>
                    <div class="card-actions">
                        @if($linkedRiskCount === 0)
                            <form action="{{ route('risk-appetites.destroy', $riskAppetite->id) }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete this appetite? This may affect linked risk reports.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete Framework</button>
                            </form>
                        @endif
                    </div>
                </div>
                <form action="{{ route('risk-appetites.update', $riskAppetite->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        
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
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $riskAppetite->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Category Filter (Optional)</label>
                                    <input type="text" name="risk_category" class="form-control" value="{{ old('risk_category', $riskAppetite->risk_category) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label required">Threshold Metric</label>
                                    <select name="threshold_metric" class="form-control" required>
                                        @foreach($metricOptions as $metricValue => $metricLabel)
                                            <option value="{{ $metricValue }}" {{ old('threshold_metric', $riskAppetite->effectiveThresholdMetric()) === $metricValue ? 'selected' : '' }}>
                                                {{ $metricLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-hint">Quantitative metrics will be used by threshold scanning when a risk links to this framework.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label required">Appetite Statement</label>
                            <textarea name="appetite_statement" class="form-control" rows="3" required>{{ old('appetite_statement', $riskAppetite->appetite_statement) }}</textarea>
                            <small class="form-hint">Qualitative description visible to risk owners for guidance.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Quantitative Target (Appetite)</label>
                                    <input type="number" step="0.01" name="appetite_limit_value" class="form-control" value="{{ old('appetite_limit_value', $riskAppetite->appetite_limit_value) }}">
                                    <small class="form-hint">Target ceiling before the risk is considered above appetite.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Maximum Tolerance Limit</label>
                                    <input type="number" step="0.01" name="tolerance_limit_value" class="form-control" value="{{ old('tolerance_limit_value', $riskAppetite->tolerance_limit_value) }}">
                                    <small class="form-hint">Hard breach boundary. This value must be greater than or equal to the appetite target.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Unit of Measure</label>
                                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $riskAppetite->unit) }}">
                                    <small class="form-hint">Use <strong>Score</strong> for score-based appetite, or a currency/unit for loss-based appetite.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $riskAppetite->is_active) ? 'checked' : '' }}>
                                <span class="form-check-label">Available for Selection (Active)</span>
                            </label>
                        </div>

                        @if($linkedRiskCount > 0)
                            <div class="alert alert-warning mb-0">
                                This framework is currently linked to <strong>{{ $linkedRiskCount }}</strong> risk(s). Deactivate it if you want to stop new selections, but deletion is blocked while links still exist.
                            </div>
                        @endif

                    </div>
                    
                    <div class="card-footer text-end">
                        <a href="{{ route('risk-appetites.index') }}" class="btn btn-link">Cancel</a>
                        <button type="submit" class="btn btn-success">Update Declaration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
