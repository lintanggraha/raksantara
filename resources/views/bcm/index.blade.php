@extends('layouts.tabler')

@section('title', 'Business Continuity Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title">Business Continuity Management (BCM)</h2>
        <div class="text-muted mt-1">Overview of Business Impact Analysis (BIA) and Recovery Plans (BCP)</div>
    </div>
</div>
@endsection

@section('content')
<div class="row row-cards mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar">
                            <i class="fas fa-project-diagram"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            Total BIAs
                        </div>
                        <div class="text-muted">
                            {{ $totalBias }} process assessments
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-danger text-white avatar">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            Tier 1 (Critical)
                        </div>
                        <div class="text-muted">
                            {{ $criticalBias }} processes
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-success text-white avatar">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            Tested BCPs
                        </div>
                        <div class="text-muted">
                            {{ $testedBcps }} plans verified
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-warning text-white avatar">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            Missing BCPs
                        </div>
                        <div class="text-muted">
                            {{ $missingBcps }} assessments need plans
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="card-title">BIA & BCP Register</h3>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create-bia">
                <i class="fas fa-plus me-1"></i> Conduct BIA
            </button>
            <form method="GET" action="{{ route('bcm.index') }}" class="d-flex gap-2">
                <select name="tier" class="form-select form-select-sm no-search-select" onchange="this.form.submit()">
                    <option value="">All Tiers</option>
                    <option value="Tier 1" {{ request('tier') == 'Tier 1' ? 'selected' : '' }}>Tier 1 (Critical)</option>
                    <option value="Tier 2" {{ request('tier') == 'Tier 2' ? 'selected' : '' }}>Tier 2</option>
                    <option value="Tier 3" {{ request('tier') == 'Tier 3' ? 'selected' : '' }}>Tier 3</option>
                    <option value="Tier 4" {{ request('tier') == 'Tier 4' ? 'selected' : '' }}>Tier 4</option>
                </select>
                @if(request('tier'))
                    <a href="{{ route('bcm.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Risk ID</th>
                    <th>Process (Risk Title)</th>
                    <th>Owner</th>
                    <th>Criticality Tier</th>
                    <th>RTO / RPO</th>
                    <th>BCP Status</th>
                    <th>Last Tested</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bias as $bia)
                    <tr>
                        <td>{{ optional($bia->risk)->risk_id }}</td>
                        <td>{{ optional($bia->risk)->risk_title }}</td>
                        <td class="text-muted">{{ \App\Support\RiskOwnerAccess::displayLabel(optional($bia->risk)->risk_owner) }}</td>
                        <td>
                            @if($bia->criticality_tier === 'Tier 1')
                                <span class="badge bg-red-lt">Tier 1</span>
                            @elseif($bia->criticality_tier === 'Tier 2')
                                <span class="badge bg-orange-lt">Tier 2</span>
                            @else
                                <span class="badge bg-secondary-lt">{{ $bia->criticality_tier ?: '-' }}</span>
                            @endif
                        </td>
                        <td class="text-muted">
                            <div>RTO: {{ $bia->rto ?: '-' }}</div>
                            <div>RPO: {{ $bia->rpo ?: '-' }}</div>
                        </td>
                        <td>
                            @if($bia->bcp)
                                <span class="badge bg-green-lt">Plan Exists</span>
                            @else
                                <span class="badge bg-yellow-lt">Missing Plan</span>
                            @endif
                        </td>
                        <td>
                            @if($bia->bcp && $bia->bcp->last_tested_at)
                                {{ $bia->bcp->last_tested_at->format('M d, Y') }}
                                @if($bia->bcp->last_test_result === 'Pass')
                                    <span class="text-success ml-1" title="Pass"><i class="fas fa-check-circle"></i></span>
                                @elseif($bia->bcp->last_test_result === 'Fail')
                                    <span class="text-danger ml-1" title="Fail"><i class="fas fa-times-circle"></i></span>
                                @else
                                    <span class="text-warning ml-1" title="Partial"><i class="fas fa-exclamation-circle"></i></span>
                                @endif
                            @else
                                <span class="text-muted">Never Tested</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('risks.show', $bia->risk_id) }}#bcm" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No Business Impact Analyses (BIA) documented yet. Go to a Risk Detail page to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bias->hasPages())
        <div class="card-footer d-flex align-items-center">
            {{ $bias->links() }}
        </div>
    @endif
</div>

<!-- Modal Create BIA -->
<div class="modal modal-blur fade" id="modal-create-bia" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Conduct BIA for Existing Process</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Business Impact Analysis (BIA) is recorded against an existing Risk / Process in the Risk Register. Please select a Process below to populate its BIA form.</p>

                @if($availableRisks->isEmpty())
                    <div class="alert alert-warning">
                        All recorded risks/processes have already been assessed with a BIA, OR there are no Risks in the register. <br><br>
                        <a href="{{ route('risks.create') }}" class="alert-link">Create a new Risk/Process first</a>.
                    </div>
                @else
                    <div class="mb-3">
                        <label class="form-label">Select Process (Risk)</label>
                        <select id="select-risk-bia" class="form-select" data-placeholder="Choose a Process...">
                            <option value=""></option>
                            @foreach($availableRisks as $r)
                                <option value="{{ $r->id }}">{{ $r->risk_id }} - {{ Str::limit($r->risk_title, 80) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                @if($availableRisks->isNotEmpty())
                    <button type="button" class="btn btn-primary" onclick="proceedToBia()">Continue to BIA Form</button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function proceedToBia() {
        var riskId = document.getElementById('select-risk-bia').value;
        if (!riskId) {
            alert('Please select a process first.');
            return;
        }
        window.location.href = '/risks/' + riskId + '#bcm';
    }
</script>
@endsection
