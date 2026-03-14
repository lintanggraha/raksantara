@extends('layouts.tabler')

@section('title', 'Mitigation Action Plan Kanban')

@section('css')
<style>
    .kanban-board {
        display: flex;
        gap: 1.5rem;
        overflow-x: auto;
        padding-bottom: 1rem;
        min-height: calc(100vh - 200px);
    }
    .kanban-column {
        flex: 1;
        min-width: 300px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        border: 1px solid #e9ecef;
    }
    .kanban-column-header {
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #dee2e6;
    }
    .kanban-header-not-started { border-bottom-color: #6c757d; }
    .kanban-header-in-progress { border-bottom-color: #0d6efd; }
    .kanban-header-blocked { border-bottom-color: #dc3545; }
    .kanban-header-completed { border-bottom-color: #198754; }
    
    .kanban-cards {
        flex: 1;
        min-height: 100px;
    }
    .kanban-card {
        background: white;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        cursor: grab;
        border-left: 4px solid transparent;
        transition: transform 0.1s, box-shadow 0.1s;
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-card:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .kanban-card.sortable-ghost {
        opacity: 0.4;
        background: #e9ecef;
    }
    .kanban-card[data-status="Not Started"] { border-left-color: #6c757d; }
    .kanban-card[data-status="In Progress"] { border-left-color: #0d6efd; }
    .kanban-card[data-status="Blocked"]     { border-left-color: #dc3545; }
    .kanban-card[data-status="Completed"]   { border-left-color: #198754; }

    .card-meta {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title">Mitigation Action Plans</h1>
        <div class="text-muted mt-1">Enterprise-wide Kanban tracking for Risk Treatments</div>
    </div>
</div>
@stop

@section('content')
<div class="kanban-board">
    @foreach(['Not Started', 'In Progress', 'Blocked', 'Completed'] as $statusColumn)
        <div class="kanban-column">
            <div class="kanban-column-header kanban-header-{{ Str::slug($statusColumn) }}">
                <span>{{ $statusColumn }}</span>
                <span class="badge bg-secondary rounded-pill" id="count-{{ Str::slug($statusColumn) }}">
                    {{ isset($kanbanData[$statusColumn]) ? $kanbanData[$statusColumn]->count() : 0 }}
                </span>
            </div>
            
            <div class="kanban-cards" data-status="{{ $statusColumn }}" id="column-{{ Str::slug($statusColumn) }}">
                @if(isset($kanbanData[$statusColumn]))
                    @foreach($kanbanData[$statusColumn] as $treatment)
                        <div class="kanban-card" data-id="{{ $treatment->id }}" data-status="{{ $statusColumn }}">
                            <div class="fw-bold text-truncate" title="{{ $treatment->option }}">{{ $treatment->option }}</div>
                            <div class="text-muted small text-truncate mt-1 mb-2">
                                <a href="{{ route('risks.show', $treatment->risk_id) }}">Risk #{{ $treatment->risk->risk_id }}</a> - {{ $treatment->risk->risk_title }}
                            </div>
                            
                            @if($treatment->description)
                                <div class="small mb-2">{{ Str::limit($treatment->description, 60) }}</div>
                            @endif

                            <div class="card-meta">
                                <span><i class="fas fa-user-circle me-1"></i> {{ $treatment->pic }}</span>
                                <span class="{{ \Carbon\Carbon::parse($treatment->deadline)->isPast() && $treatment->status !== 'Completed' ? 'text-danger fw-bold' : '' }}">
                                    <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($treatment->deadline)->format('d M y') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach
</div>
@stop

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const columns = document.querySelectorAll('.kanban-cards');
        
        columns.forEach(column => {
            new Sortable(column, {
                group: 'kanban', // set both lists to same group
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function (evt) {
                    const itemEl = evt.item; // dragged HTMLElement
                    const toColumn = evt.to; // target list
                    const newStatus = toColumn.getAttribute('data-status');
                    const treatmentId = itemEl.getAttribute('data-id');
                    
                    // Optimistic UI update
                    itemEl.setAttribute('data-status', newStatus);
                    updateCounts();

                    // AJAX request to update database
                    fetch(`/mitigation-kanban/${treatmentId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: newStatus })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Success:', data.message);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to update status. The page will reload.');
                        location.reload();
                    });
                },
            });
        });

        function updateCounts() {
            const statuses = ['Not Started', 'In Progress', 'Blocked', 'Completed'];
            statuses.forEach(status => {
                const slug = status.toLowerCase().replace(/ /g, '-');
                const count = document.querySelectorAll(`#column-${slug} .kanban-card`).length;
                document.getElementById(`count-${slug}`).textContent = count;
            });
        }
    });
</script>
@endpush
