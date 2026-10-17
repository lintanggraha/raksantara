@extends('layouts.tabler')

@section('title', 'Mitigation Action Plan Kanban')

@section('css')
<style>
    .kanban-board {
        display: flex;
        gap: 1.25rem;
        overflow-x: auto;
        padding-bottom: 1rem;
        min-height: calc(100vh - 220px);
    }
    .kanban-column {
        flex: 1 1 0;
        min-width: 250px;
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        border: 1px solid #dbe5f0;
    }
    .kanban-column-header {
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.65rem;
        border-bottom: 2px solid #dee2e6;
    }
    .kanban-header-not-started { border-bottom-color: #64748b; }
    .kanban-header-in-progress { border-bottom-color: #2563eb; }
    .kanban-header-blocked { border-bottom-color: #dc2626; }
    .kanban-header-completed { border-bottom-color: #16a34a; }
    .kanban-cards {
        flex: 1;
        min-height: 120px;
    }
    .kanban-card {
        min-width: 0;
        overflow: hidden;
        background: #ffffff;
        border-radius: 10px;
        padding: 0.95rem;
        margin-bottom: 0.85rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        cursor: grab;
        border-left: 4px solid transparent;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }
    .kanban-card:hover {
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
        transform: translateY(-2px);
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-card.sortable-ghost {
        opacity: 0.35;
        background: #e2e8f0;
    }
    .kanban-card[data-status="Not Started"] { border-left-color: #64748b; }
    .kanban-card[data-status="In Progress"] { border-left-color: #2563eb; }
    .kanban-card[data-status="Blocked"] { border-left-color: #dc2626; }
    .kanban-card[data-status="Completed"] { border-left-color: #16a34a; }
    .kanban-readonly .kanban-card,
    .kanban-readonly .kanban-card:active {
        cursor: default;
    }
    .kanban-readonly .kanban-card:hover {
        transform: none;
    }
    .kanban-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.65rem;
    }
    .kanban-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .kanban-chip-sla-overdue { background: #fee2e2; color: #b91c1c; }
    .kanban-chip-sla-due-soon { background: #fef3c7; color: #92400e; }
    .kanban-chip-sla-on-track { background: #dbeafe; color: #1d4ed8; }
    .kanban-chip-sla-completed { background: #dcfce7; color: #166534; }
    .kanban-chip-sla-no-deadline { background: #e5e7eb; color: #374151; }
    .kanban-chip-aging { background: #e2e8f0; color: #334155; }
    .kanban-chip-evidence { background: #ecfccb; color: #3f6212; }
    .kanban-card-note {
        margin-top: 0.75rem;
        padding: 0.65rem 0.75rem;
        border-radius: 8px;
        background: #fff7ed;
        color: #9a3412;
        font-size: 0.8rem;
    }
    .kanban-card-checklist {
        margin-top: 0.75rem;
        padding: 0.65rem 0.75rem;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        font-size: 0.8rem;
        color: #334155;
        white-space: pre-line;
    }
    .kanban-rules-alert {
        margin-bottom: 1rem;
        padding: 0.85rem 1rem;
        border-radius: 10px;
        background: #eaf3ff;
        border: 1px solid #c6d8f1;
        color: #19324d;
    }
    .kanban-rules-alert strong {
        color: #10263d;
    }
</style>
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title">Mitigation Action Plans</h1>
        <div class="text-muted mt-1">Operational kanban for treatment aging, SLA tracking, evidence readiness, and next actions.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('owners.workload') }}" class="btn btn-outline-secondary btn-sm with-icon">
            <i class="fas fa-users-cog"></i> Owner Workload
        </a>
        <a href="{{ route('treatment-templates.index') }}" class="btn btn-outline-secondary btn-sm with-icon">
            <i class="fas fa-book"></i> Playbooks
        </a>
    </div>
</div>
@stop

@section('content')
@if(!$canManage)
    <div class="alert alert-info mb-3">
        Your account is read-only for mitigation updates.
    </div>
@endif

<div class="kanban-rules-alert">
    <strong>Operational rules:</strong> blocked items should carry a blocked reason, and completed items require at least one evidence attachment.
</div>

<div class="kanban-board {{ $canManage ? '' : 'kanban-readonly' }}">
    @foreach(['Not Started', 'In Progress', 'Blocked', 'Completed'] as $statusColumn)
        <div class="kanban-column">
            <div class="kanban-column-header kanban-header-{{ Str::slug($statusColumn) }}">
                <span>{{ $statusColumn }}</span>
                <span class="badge bg-secondary rounded-pill" id="count-{{ Str::slug($statusColumn) }}">
                    {{ isset($kanbanData[$statusColumn]) ? $kanbanData[$statusColumn]->count() : 0 }}
                </span>
            </div>
            
            <div class="kanban-cards" data-status="{{ $statusColumn }}" id="column-{{ Str::slug($statusColumn) }}">
                @forelse(($kanbanData[$statusColumn] ?? collect()) as $treatment)
                    @php
                        $slaClass = 'kanban-chip-sla-on-track';
                        if ($treatment->sla_state === 'Overdue') {
                            $slaClass = 'kanban-chip-sla-overdue';
                        } elseif ($treatment->sla_state === 'Due Soon') {
                            $slaClass = 'kanban-chip-sla-due-soon';
                        } elseif ($treatment->sla_state === 'Completed') {
                            $slaClass = 'kanban-chip-sla-completed';
                        } elseif ($treatment->sla_state === 'No Deadline') {
                            $slaClass = 'kanban-chip-sla-no-deadline';
                        }
                    @endphp
                    <div
                        class="kanban-card"
                        data-id="{{ $treatment->id }}"
                        data-status="{{ $statusColumn }}"
                        data-blocked-reason="{{ $treatment->blocked_reason ?? '' }}"
                        data-next-action-at="{{ optional($treatment->next_action_at)->format('Y-m-d') ?? '' }}"
                    >
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-bold text-truncate" title="{{ $treatment->option }}">{{ $treatment->option }}</div>
                                <div class="text-muted small text-truncate mt-1" title="{{ $treatment->risk->risk_id }} - {{ $treatment->risk->risk_title }}">
                                    <a href="{{ route('risks.show', $treatment->risk_id) }}">Risk #{{ $treatment->risk->risk_id }}</a> - {{ $treatment->risk->risk_title }}
                                </div>
                            </div>
                            @if($treatment->template)
                                <span class="badge bg-blue-lt">{{ $treatment->template->name }}</span>
                            @endif
                        </div>
                        
                        @if($treatment->description)
                            <div class="small mt-2">{{ Str::limit($treatment->description, 110) }}</div>
                        @endif

                        <div class="kanban-chip-row">
                            <span class="kanban-chip {{ $slaClass }}">
                                <i class="fas fa-hourglass-half"></i> {{ $treatment->sla_state }}
                            </span>
                            <span class="kanban-chip kanban-chip-aging">
                                <i class="fas fa-stopwatch"></i> {{ (int) $treatment->aging_days }}d in status
                            </span>
                            <span class="kanban-chip kanban-chip-evidence">
                                <i class="fas fa-paperclip"></i> {{ (int) $treatment->attachments_count }} evidence
                            </span>
                        </div>

                        <div class="mt-3 small">
                            <div><strong>PIC:</strong> {{ $treatment->pic }}</div>
                            <div><strong>Deadline:</strong> {{ optional($treatment->deadline)->format('d M Y') ?? '-' }}</div>
                            <div><strong>Next Action:</strong> {{ optional($treatment->next_action_at)->format('d M Y') ?? '-' }}</div>
                        </div>

                        @if($treatment->blocked_reason)
                            <div class="kanban-card-note">
                                <strong>Blocked reason:</strong> {{ Str::limit($treatment->blocked_reason, 140) }}
                            </div>
                        @endif

                        @if(trim((string) $treatment->playbook_checklist) !== '')
                            <div class="kanban-card-checklist">
                                <strong>Checklist</strong>
                                <div class="mt-1">{{ Str::limit($treatment->playbook_checklist, 220) }}</div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-muted small">No actions in this column.</div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
@stop

@push('scripts')
@if($canManage)
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const columns = document.querySelectorAll('.kanban-cards');

            columns.forEach(column => {
                new Sortable(column, {
                    group: 'kanban',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function (evt) {
                        const itemEl = evt.item;
                        const toColumn = evt.to;
                        const newStatus = toColumn.getAttribute('data-status');
                        const treatmentId = itemEl.getAttribute('data-id');
                        const payload = { status: newStatus };

                        if (newStatus === 'Blocked') {
                            const blockedReason = window.prompt(
                                'Blocked reason is required for this move.',
                                itemEl.getAttribute('data-blocked-reason') || ''
                            );

                            if (blockedReason === null || blockedReason.trim() === '') {
                                location.reload();
                                return;
                            }

                            payload.blocked_reason = blockedReason.trim();
                        }

                        if (newStatus !== 'Completed') {
                            const nextActionAt = itemEl.getAttribute('data-next-action-at') || '';
                            if (nextActionAt) {
                                payload.next_action_at = nextActionAt;
                            }
                        }

                        fetch(`/mitigation-kanban/${treatmentId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(async response => {
                            if (!response.ok) {
                                const data = await response.json().catch(() => ({}));
                                const message = data.message
                                    || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Failed to update treatment.');
                                throw new Error(message);
                            }

                            return response.json();
                        })
                        .then(() => {
                            location.reload();
                        })
                        .catch(error => {
                            alert(error.message || 'Failed to update treatment. The page will reload.');
                            location.reload();
                        });
                    },
                });
            });
        });
    </script>
@endif
@endpush
