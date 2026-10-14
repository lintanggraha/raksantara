@extends('layouts.tabler')

@section('title', 'Third-Party Vendors')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="mb-0">Third-Party Vendors</h1>
    <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-sm with-icon">
        <i class="fas fa-plus"></i> Add Vendor
    </a>
</div>
@stop

@section('content')
<div class="card shadow">
    <div class="table-responsive">
        <table class="table table-vcenter mb-0">
            <thead>
                <tr>
                    <th>Vendor Name</th>
                    <th>Contact</th>
                    <th>Criticality Level</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $vendor->name }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $vendor->description ?: '-' }}</div>
                        </td>
                        <td>
                            <div>{{ $vendor->contact_name ?: '-' }}</div>
                            <div class="text-muted small">{{ $vendor->contact_email ?: '-' }}</div>
                        </td>
                        <td>
                            @if($vendor->criticality_level === 'High')
                                <span class="badge bg-red-lt">High</span>
                            @elseif($vendor->criticality_level === 'Medium')
                                <span class="badge bg-yellow-lt">Medium</span>
                            @elseif($vendor->criticality_level === 'Low')
                                <span class="badge bg-green-lt">Low</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($vendor->status === 'Active')
                                <span class="badge bg-green-lt">Active</span>
                            @else
                                <span class="badge bg-secondary-lt">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('vendors.show', $vendor->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                                <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No vendors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($vendors->hasPages())
        <div class="card-footer">
            {{ $vendors->links() }}
        </div>
    @endif
</div>
@stop
