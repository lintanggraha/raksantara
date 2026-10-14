<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Key Risk Indicators (KRIs)</strong>
        @if($canManage)
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addKriModal">
                <i class="fas fa-plus"></i> Add KRI
            </button>
        @endif
    </div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Metric Name</th>
                    <th>Type</th>
                    <th>Current Value</th>
                    <th>Status</th>
                    <th>Thresholds (Warn/Crit)</th>
                    <th>Last Updated</th>
                    @if($canManage)
                        <th style="width: 150px;">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($risk->kris as $kri)
                    <tr>
                        <td>
                            <strong>{{ $kri->metric_name }}</strong><br>
                            <small class="text-muted">{{ $kri->description }}</small>
                        </td>
                        <td>{{ $kri->threshold_type === 'lower_is_better' ? 'Lower is Better' : 'Higher is Better' }}</td>
                        <td class="text-center font-weight-bold">
                            {{ $kri->current_value !== null ? $kri->current_value : '-' }}
                        </td>
                        <td class="text-center">
                            @if($kri->current_value === null)
                                <span class="badge bg-secondary-lt">No data yet</span>
                            @elseif($kri->status === 'Red')
                                <span class="badge bg-danger">Red</span>
                            @elseif($kri->status === 'Amber')
                                <span class="badge bg-warning text-dark">Amber</span>
                            @else
                                <span class="badge bg-success">Green</span>
                            @endif
                        </td>
                        <td>
                            W: {{ $kri->threshold_warn ?? '-' }}<br>
                            C: {{ $kri->threshold_critical ?? '-' }}
                        </td>
                        <td>
                            {{ optional($kri->updater)->name ?? '-' }}<br>
                            <small class="text-muted">{{ $kri->updated_at->format('d-m-Y H:i') }}</small>
                        </td>
                        @if($canManage)
                            <td>
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#logKriModal{{ $kri->id }}" title="Log Value">
                                        <i class="fas fa-clipboard-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#editKriModal{{ $kri->id }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('kris.destroy', $kri->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this KRI?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canManage ? 7 : 6 }}" class="text-center text-muted">No Key Risk Indicators found for this risk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($canManage)
    <!-- Add KRI Modal -->
    <div class="modal fade" id="addKriModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('kris.store', $risk->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Key Risk Indicator</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Metric Name</label>
                            <input type="text" name="metric_name" class="form-control" placeholder="e.g., Server Uptime %" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Threshold Direction</label>
                            <select name="threshold_type" class="form-select" required>
                                <option value="higher_is_better">Higher is Better (e.g. Uptime > 99%)</option>
                                <option value="lower_is_better" selected>Lower is Better (e.g. Turnaround Time < 2 days)</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Warning Threshold (Amber)</label>
                                <input type="number" step="0.01" name="threshold_warn" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Critical Threshold (Red)</label>
                                <input type="number" step="0.01" name="threshold_critical" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save KRI</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit and Log Modals for each KRI -->
    @foreach($risk->kris as $kri)
        <!-- Edit KRI Modal -->
        <div class="modal fade" id="editKriModal{{ $kri->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('kris.update', $kri->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Key Risk Indicator</h5>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">Metric Name</label>
                                <input type="text" name="metric_name" class="form-control" value="{{ $kri->metric_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ $kri->description }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Threshold Direction</label>
                                <select name="threshold_type" class="form-select" required>
                                    <option value="higher_is_better" {{ $kri->threshold_type === 'higher_is_better' ? 'selected' : '' }}>Higher is Better</option>
                                    <option value="lower_is_better" {{ $kri->threshold_type === 'lower_is_better' ? 'selected' : '' }}>Lower is Better</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Warning Threshold (Amber)</label>
                                    <input type="number" step="0.01" name="threshold_warn" class="form-control" value="{{ $kri->threshold_warn }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Critical Threshold (Red)</label>
                                    <input type="number" step="0.01" name="threshold_critical" class="form-control" value="{{ $kri->threshold_critical }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update KRI</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Log Value Modal -->
        <div class="modal fade" id="logKriModal{{ $kri->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <form action="{{ route('kris.logValue', $kri->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Log Reading: {{ $kri->metric_name }}</h5>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">New Value</label>
                                <input type="number" step="0.01" name="recorded_value" class="form-control font-weight-bold" placeholder="e.g. 99.9" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Log Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Optional context for this reading..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Save Log</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif
