@extends('layouts.tabler')

@section('title', 'Risk Details')

@section('content_header')
@php
    $currentUser = auth()->user();
    $isSuperadmin = $currentUser->isSuperadmin();
    $isRiskCreator = (int) ($risk->created_by ?? 0) > 0
        && (int) $risk->created_by === (int) $currentUser->id;
    $canManageLegacyRisk = (int) ($risk->created_by ?? 0) <= 0
        && $currentUser->canManageRiskOwner($risk->risk_owner);
    $canManage = !$isSuperadmin && ($isRiskCreator || $canManageLegacyRisk);
    $canComment = !$isSuperadmin;
@endphp
<div class="d-flex justify-content-between align-items-center">
    <h1>Risk Details - {{ $risk->risk_id }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('risks.rcsa.show', $risk->id) }}" class="btn btn-info btn-sm with-icon">
            <i class="fas fa-clipboard-check"></i> RCSA Workspace
        </a>
        @if($canManage)
            <a href="{{ route('risks.edit', $risk->id) }}" class="btn btn-warning btn-sm with-icon">
                <i class="fas fa-edit"></i> Edit
            </a>
        @endif
        <a href="{{ route('risks.index') }}" class="btn btn-secondary btn-sm with-icon">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>
@stop

@section('css')
<style>
    .mention-field-wrap {
        position: relative;
    }

    .mention-highlight-layer {
        display: none;
        position: absolute;
        inset: 0;
        z-index: 1;
        overflow: auto;
        pointer-events: none;
        color: #1f2937;
        background: #ffffff;
        border: 1px solid #c6d0dd;
        border-radius: .25rem;
        box-sizing: border-box;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .mention-highlight-token {
        display: inline;
        color: #1d4ed8;
        background: #dbeafe;
        border-radius: 4px;
        padding: 0 1px;
    }

    .mention-field-wrap.is-highlight-ready .mention-input {
        position: relative;
        z-index: 2;
        background: transparent !important;
        color: transparent !important;
        caret-color: #0f172a;
        border-color: transparent !important;
        resize: vertical;
    }

    .mention-field-wrap.is-highlight-ready .mention-input::placeholder {
        color: #6b7280 !important;
    }

    .mention-field-wrap.is-highlight-ready .mention-input:focus {
        box-shadow: none;
    }

    .mention-field-wrap.is-highlight-ready .mention-input:focus + .mention-highlight-layer {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .25);
    }

    .mention-field-wrap.is-highlight-ready .mention-highlight-layer {
        display: block;
    }

    .mention-suggestion-list {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1080;
        display: none;
        max-height: 220px;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #d6deea;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
    }

    .mention-suggestion-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        border: 0;
        border-bottom: 1px solid #edf2f7;
        padding: 8px 10px;
        text-align: left;
        background: #ffffff;
        color: #1f2937;
        cursor: pointer;
        font-size: 0.85rem;
    }

    .mention-suggestion-item:last-child {
        border-bottom: 0;
    }

    .mention-suggestion-item:hover,
    .mention-suggestion-item.is-active {
        background: #eef3ff;
    }

    .mention-suggestion-name {
        font-weight: 600;
        color: #0f172a;
    }

    .mention-suggestion-username {
        color: #4b5563;
        white-space: nowrap;
    }
</style>
@stop

@php
    $mentionOptions = ($mentionUsers ?? collect())
        ->map(function ($user) {
            return [
                'username' => (string) ($user->username ?? ''),
                'name' => (string) ($user->name ?? ''),
            ];
        })
        ->filter(function ($user) {
            return trim($user['username']) !== '';
        })
        ->values();
@endphp

@section('js')
<script>
    (() => {
        const mentionUsers = @json($mentionOptions);
        const mentionInputs = document.querySelectorAll('.mention-input');
        if (!Array.isArray(mentionUsers) || mentionUsers.length === 0 || mentionInputs.length === 0) {
            return;
        }

        const mentionRegex = /(^|[\s([{])@([A-Za-z0-9_.-]{1,255})$/;
        const mentionTokenRegex = /(^|[\s([{])(@[A-Za-z0-9_.-]+)/g;
        let activeInstance = null;

        const normalize = (value) => String(value || '').trim().toLowerCase();
        const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (char) => {
            switch (char) {
                case '&': return '&amp;';
                case '<': return '&lt;';
                case '>': return '&gt;';
                case '"': return '&quot;';
                case "'": return '&#39;';
                default: return char;
            }
        });

        const userRows = mentionUsers
            .map((user) => ({
                username: String(user.username || '').trim(),
                name: String(user.name || '').trim()
            }))
            .filter((user) => user.username !== '');

        function buildHighlightContent(value) {
            const text = String(value || '');
            if (text === '') {
                return '&nbsp;';
            }

            const regex = new RegExp(mentionTokenRegex.source, 'g');
            let html = '';
            let lastIndex = 0;
            let match = regex.exec(text);

            while (match !== null) {
                const prefix = String(match[1] || '');
                const token = String(match[2] || '');
                const matchStart = match.index;
                const tokenStart = matchStart + prefix.length;

                html += escapeHtml(text.slice(lastIndex, matchStart));
                html += escapeHtml(prefix);
                html += `<span class="mention-highlight-token">${escapeHtml(token)}</span>`;
                lastIndex = tokenStart + token.length;
                match = regex.exec(text);
            }

            html += escapeHtml(text.slice(lastIndex));
            return html === '' ? '&nbsp;' : html;
        }

        function syncHighlightStyle(instance) {
            if (!instance.highlight) {
                return;
            }

            const textareaStyles = window.getComputedStyle(instance.input);
            instance.highlight.style.fontFamily = textareaStyles.fontFamily;
            instance.highlight.style.fontSize = textareaStyles.fontSize;
            instance.highlight.style.fontWeight = textareaStyles.fontWeight;
            instance.highlight.style.lineHeight = textareaStyles.lineHeight;
            instance.highlight.style.letterSpacing = textareaStyles.letterSpacing;
            instance.highlight.style.paddingTop = textareaStyles.paddingTop;
            instance.highlight.style.paddingRight = textareaStyles.paddingRight;
            instance.highlight.style.paddingBottom = textareaStyles.paddingBottom;
            instance.highlight.style.paddingLeft = textareaStyles.paddingLeft;
            instance.highlight.style.borderRadius = textareaStyles.borderRadius;
            instance.highlight.style.borderWidth = textareaStyles.borderWidth;
        }

        function syncHighlightScroll(instance) {
            if (!instance.highlight) {
                return;
            }

            instance.highlight.scrollTop = instance.input.scrollTop;
            instance.highlight.scrollLeft = instance.input.scrollLeft;
        }

        function refreshHighlight(instance) {
            if (!instance.highlight) {
                return;
            }

            instance.highlight.innerHTML = buildHighlightContent(instance.input.value);
            syncHighlightScroll(instance);
        }

        function closeInstance(instance) {
            if (!instance) {
                return;
            }
            instance.results = [];
            instance.activeIndex = 0;
            instance.mentionState = null;
            instance.list.style.display = 'none';
            instance.list.setAttribute('aria-hidden', 'true');
            instance.list.innerHTML = '';
            if (activeInstance === instance) {
                activeInstance = null;
            }
        }

        function closeAllInstances() {
            instances.forEach((instance) => closeInstance(instance));
        }

        function scoreUser(user, query) {
            const username = normalize(user.username);
            const name = normalize(user.name);
            if (username.startsWith(query)) {
                return 0;
            }
            if (name.startsWith(query)) {
                return 1;
            }
            const words = name.split(/\s+/).filter(Boolean);
            if (words.some((word) => word.startsWith(query))) {
                return 2;
            }
            return -1;
        }

        function getMentionState(textarea) {
            const cursorPos = textarea.selectionStart ?? 0;
            const beforeCursor = textarea.value.slice(0, cursorPos);
            const lineStart = beforeCursor.lastIndexOf('\n') + 1;
            const segment = beforeCursor.slice(lineStart);
            const match = segment.match(mentionRegex);

            if (!match) {
                return null;
            }

            const query = normalize(match[2]);
            if (query.length < 1) {
                return null;
            }

            return {
                query,
                start: beforeCursor.length - (match[2].length + 1),
                end: beforeCursor.length
            };
        }

        function findUsers(query) {
            return userRows
                .map((user) => ({ user, score: scoreUser(user, query) }))
                .filter((row) => row.score >= 0)
                .sort((a, b) => {
                    if (a.score !== b.score) {
                        return a.score - b.score;
                    }
                    return a.user.username.localeCompare(b.user.username);
                })
                .map((row) => row.user);
        }

        function setActiveItem(instance, index) {
            instance.activeIndex = index;
            const items = instance.list.querySelectorAll('.mention-suggestion-item');
            items.forEach((item, itemIndex) => {
                item.classList.toggle('is-active', itemIndex === index);
            });
        }

        function renderSuggestions(instance) {
            if (!instance.results.length) {
                closeInstance(instance);
                return;
            }

            instance.list.innerHTML = instance.results
                .map((user, index) => `
                    <button type="button" class="mention-suggestion-item${index === 0 ? ' is-active' : ''}" data-index="${index}">
                        <span class="mention-suggestion-name">${escapeHtml(user.name || user.username)}</span>
                        <span class="mention-suggestion-username">@${escapeHtml(user.username)}</span>
                    </button>
                `)
                .join('');

            instance.activeIndex = 0;
            instance.list.style.display = 'block';
            instance.list.setAttribute('aria-hidden', 'false');
            activeInstance = instance;
        }

        function replaceMention(instance, user) {
            if (!instance.mentionState) {
                return;
            }

            const textarea = instance.input;
            const value = textarea.value;
            const mentionText = `@${user.username} `;

            const newValue =
                value.slice(0, instance.mentionState.start) +
                mentionText +
                value.slice(instance.mentionState.end);

            const cursorPos = instance.mentionState.start + mentionText.length;

            textarea.value = newValue;
            textarea.focus();
            textarea.setSelectionRange(cursorPos, cursorPos);
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            closeInstance(instance);
        }

        function refreshSuggestions(instance) {
            const mentionState = getMentionState(instance.input);
            if (!mentionState) {
                closeInstance(instance);
                return;
            }

            const results = findUsers(mentionState.query);
            if (!results.length) {
                closeInstance(instance);
                return;
            }

            instance.mentionState = mentionState;
            instance.results = results;
            renderSuggestions(instance);
        }

        const instances = Array.from(mentionInputs)
            .map((input) => {
                const container = input.closest('.mention-field-wrap');
                const highlight = container ? container.querySelector('.mention-highlight-layer') : null;
                const list = container ? container.querySelector('.mention-suggestion-list') : null;
                if (!container || !list) {
                    return null;
                }

                const instance = {
                    input,
                    highlight,
                    list,
                    mentionState: null,
                    results: [],
                    activeIndex: 0
                };

                if (highlight) {
                    container.classList.add('is-highlight-ready');
                    syncHighlightStyle(instance);
                    refreshHighlight(instance);
                }

                input.addEventListener('input', () => {
                    refreshHighlight(instance);
                    refreshSuggestions(instance);
                });
                input.addEventListener('click', () => refreshSuggestions(instance));
                input.addEventListener('focus', () => refreshSuggestions(instance));
                input.addEventListener('scroll', () => syncHighlightScroll(instance));
                input.addEventListener('keydown', (event) => {
                    if (activeInstance !== instance || instance.results.length === 0) {
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        const nextIndex = (instance.activeIndex + 1) % instance.results.length;
                        setActiveItem(instance, nextIndex);
                        return;
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        const nextIndex = (instance.activeIndex - 1 + instance.results.length) % instance.results.length;
                        setActiveItem(instance, nextIndex);
                        return;
                    }

                    if (event.key === 'Enter' || event.key === 'Tab') {
                        event.preventDefault();
                        const selected = instance.results[instance.activeIndex];
                        if (selected) {
                            replaceMention(instance, selected);
                        }
                        return;
                    }

                    if (event.key === 'Escape') {
                        event.preventDefault();
                        closeInstance(instance);
                    }
                });

                list.addEventListener('mousedown', (event) => {
                    const item = event.target.closest('.mention-suggestion-item');
                    if (!item) {
                        return;
                    }
                    event.preventDefault();
                    const index = Number(item.dataset.index);
                    if (!Number.isNaN(index) && instance.results[index]) {
                        replaceMention(instance, instance.results[index]);
                    }
                });

                list.addEventListener('mousemove', (event) => {
                    const item = event.target.closest('.mention-suggestion-item');
                    if (!item) {
                        return;
                    }
                    const index = Number(item.dataset.index);
                    if (!Number.isNaN(index)) {
                        setActiveItem(instance, index);
                    }
                });

                return instance;
            })
            .filter(Boolean);

        if (!instances.length) {
            return;
        }

        window.addEventListener('resize', () => {
            instances.forEach((instance) => {
                syncHighlightStyle(instance);
                syncHighlightScroll(instance);
            });
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.mention-field-wrap')) {
                closeAllInstances();
            }
        });
    })();
</script>
@stop

@section('content')
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" href="#basic" role="tab" data-toggle="tab">Basic</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#inherent" role="tab" data-toggle="tab">Inherent</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#treatment" role="tab" data-toggle="tab">Treatment</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#rcsa" role="tab" data-toggle="tab">RCSA</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#residual" role="tab" data-toggle="tab">Residual</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#bcm" role="tab" data-toggle="tab">BCM (BIA/BCP)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#governance" role="tab" data-toggle="tab">Governance</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#kris" role="tab" data-toggle="tab">Key Risk Indicators</a>
    </li>
</ul>

<div class="tab-content mt-3">
    <div role="tabpanel" class="tab-pane active" id="basic">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Risk ID</strong><div>{{ $risk->risk_id }}</div></div>
                    <div class="col-md-3"><strong>Year</strong><div>{{ $risk->year }}</div></div>
                    <div class="col-md-6"><strong>Process</strong><div>{{ $risk->risk_title }}</div></div>
                    <div class="col-md-12"><strong>Risk Identification</strong><div>{{ $risk->risk_description ?? '-' }}</div></div>
                    <div class="col-md-4"><strong>Category</strong><div>{{ optional($risk->category)->name ?? '-' }}</div></div>
                    <div class="col-md-4"><strong>Cause Category</strong><div>{{ optional($risk->causeCategory)->name ?? '-' }}</div></div>
                    <div class="col-md-4">
                        <strong>Third-Party Vendor</strong>
                        <div>
                            @if($risk->vendor)
                                <a href="{{ route('vendors.show', $risk->vendor_id) }}">{{ $risk->vendor->name }}</a>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <strong>Corporate Risk Appetite</strong>
                        <div>
                            @if($risk->riskAppetite)
                                <a href="{{ route('risk-appetites.show', $risk->riskAppetite->id) }}">{{ $risk->riskAppetite->name }}</a>
                                @if($risk->riskAppetite->tolerance_limit_value !== null)
                                    <span class="badge bg-danger ms-1" title="Tolerance Limit">
                                        Max: {{ number_format($risk->riskAppetite->tolerance_limit_value, 0) }}
                                        {{ $risk->riskAppetite->effectiveThresholdMetric() === \App\RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE ? 'Score' : $risk->riskAppetite->unit }}
                                    </span>
                                @endif
                                @if(!empty($appetiteEvaluation['status_label']))
                                    <div class="mt-1">
                                        <span class="badge {{ $appetiteEvaluation['badge_class'] ?? 'bg-secondary-lt' }}">
                                            {{ $appetiteEvaluation['status_label'] }}
                                        </span>
                                    </div>
                                @endif
                                @if(!empty($appetiteEvaluation['metric_label']))
                                    <div class="small text-muted mt-1">
                                        Threshold engine: {{ $appetiteEvaluation['metric_label'] }}
                                        @if(!is_null($appetiteEvaluation['warning_threshold']) || !is_null($appetiteEvaluation['breach_threshold']))
                                            |
                                            Appetite: {{ $appetiteEvaluation['warning_threshold'] ?? '-' }}
                                            {{ $appetiteEvaluation['unit'] }}
                                            |
                                            Tolerance: {{ $appetiteEvaluation['breach_threshold'] ?? '-' }}
                                            {{ $appetiteEvaluation['unit'] }}
                                        @endif
                                    </div>
                                @endif
                                @if(!empty($appetiteEvaluation['message']))
                                    <div class="small text-muted mt-1">{{ $appetiteEvaluation['message'] }}</div>
                                @endif
                            @else
                                <span class="text-muted">Not specified</span>
                                @if(!empty($appetiteEvaluation['source_label']))
                                    <div class="small text-muted mt-1">Threshold source: {{ $appetiteEvaluation['source_label'] }}</div>
                                @endif
                                @if(!empty($appetiteEvaluation['message']))
                                    <div class="small text-muted mt-1">{{ $appetiteEvaluation['message'] }}</div>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4"><strong>Owner</strong><div>{{ \App\Support\RiskOwnerAccess::displayLabel($risk->risk_owner) }}</div></div>
                    <div class="col-md-8"><strong>Cause</strong><div>{{ $risk->cause ?? '-' }}</div></div>
                    <div class="col-md-8"><strong>Existing Control</strong><div>{{ $risk->existing_control ?? '-' }}</div></div>
                    <div class="col-md-4"><strong>Status</strong>
                        <div>
                            @if($risk->status === 'Open')
                                <span class="badge bg-red-lt">Open</span>
                            @elseif($risk->status === 'Monitoring')
                                <span class="badge bg-yellow-lt">Monitoring</span>
                            @else
                                <span class="badge bg-green-lt">Closed</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($risk->statusChangeRequests->where('status', 'pending')->isNotEmpty())
                    <div class="alert alert-warning mt-3 mb-0">
                        Pending status change request exists.
                        @if($isSuperadmin)
                            <a href="{{ route('risk-status-approvals.index') }}" class="alert-link">Open approval queue</a>.
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="inherent">
        <div class="card">
            <div class="card-body">
                @if($risk->inherentAnalysis)
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Probability Description</strong><div>{{ $risk->inherentAnalysis->probability_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Probability Score</strong><div>{{ $risk->inherentAnalysis->probability_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Impact Description</strong><div>{{ $risk->inherentAnalysis->impact_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Impact Score</strong><div>{{ $risk->inherentAnalysis->impact_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Inherent Risk Score</strong><div>{{ $risk->inherentAnalysis->inherent_risk_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Risk Level</strong><div>{{ $risk->inherentAnalysis->risk_level ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Inherent Impact (Rp)</strong><div>{{ is_null($risk->inherentAnalysis->impact_amount) ? '-' : number_format((float) $risk->inherentAnalysis->impact_amount, 0, ',', '.') }}</div></div>
                        <div class="col-md-6"><strong>Inherent Probability (%)</strong><div>{{ is_null($risk->inherentAnalysis->probability_percent) ? '-' : number_format(((float) $risk->inherentAnalysis->probability_percent) * 100, 2, '.', '') . '%' }}</div></div>
                        <div class="col-md-6"><strong>Inherent Potential Loss (Rp)</strong><div>{{ is_null($risk->inherentAnalysis->potential_loss) ? '-' : number_format((float) $risk->inherentAnalysis->potential_loss, 0, ',', '.') }}</div></div>
                    </div>
                @else
                    <div class="text-muted">No inherent analysis data.</div>
                @endif
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="treatment">
        <div class="card">
            <div class="card-body">
                @if($risk->treatments->isEmpty())
                    <div class="text-muted">No treatment options.</div>
                @else
                    @foreach($risk->treatments as $treatment)
                        <div class="border rounded p-3 mb-3">
                            <div class="row g-2">
                                <div class="col-md-2"><strong>Option</strong><div>{{ $treatment->option }}</div></div>
                                <div class="col-md-4"><strong>Description</strong><div>{{ $treatment->description ?? '-' }}</div></div>
                                <div class="col-md-2"><strong>PIC</strong><div>{{ $treatment->pic }}</div></div>
                                <div class="col-md-2"><strong>Deadline</strong><div>{{ $treatment->deadline ? \Carbon\Carbon::parse($treatment->deadline)->format('d-m-Y') : '-' }}</div></div>
                                <div class="col-md-2"><strong>Effectiveness</strong><div>{{ $treatment->effectiveness }}</div></div>
                                <div class="col-md-3"><strong>Playbook</strong><div>{{ optional($treatment->template)->name ?? '-' }}</div></div>
                                <div class="col-md-2"><strong>Effectiveness Score</strong><div>{{ $treatment->effectiveness_score ?? '-' }}</div></div>
                                <div class="col-md-2"><strong>Status</strong><div>{{ $treatment->status ?? '-' }}</div></div>
                                <div class="col-md-3"><strong>Next Action</strong><div>{{ optional($treatment->next_action_at)->format('d-m-Y') ?? '-' }}</div></div>
                                <div class="col-md-5"><strong>Blocked Reason</strong><div>{{ $treatment->blocked_reason ?: '-' }}</div></div>
                                <div class="col-md-12"><strong>Checklist</strong><div>{!! nl2br(e($treatment->playbook_checklist ?: '-')) !!}</div></div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Treatment Evidence</strong>
                                @if($canManage)
                                    <form class="d-flex gap-2" action="{{ route('risks.treatments.attachments.store', [$risk->id, $treatment->id]) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" name="file" class="form-control form-control-sm" required>
                                        <input type="text" name="notes" class="form-control form-control-sm" maxlength="1000" placeholder="Notes">
                                        <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                                    </form>
                                @endif
                            </div>
                            <div class="mt-2">
                                @forelse($treatment->attachments as $attachment)
                                    <div class="d-flex justify-content-between align-items-center border rounded px-2 py-1 mb-1">
                                        <div>
                                            <strong>{{ $attachment->original_name }}</strong>
                                            <div class="text-muted small">
                                                {{ optional($attachment->uploader)->name ?? 'System' }}
                                                - {{ optional($attachment->created_at)->format('d-m-Y H:i') ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                            @if($canManage)
                                                <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this attachment?')">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">No treatment evidence.</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="rcsa">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <div class="fw-bold">RCSA Workspace</div>
                        <div class="text-muted small">
                            @if($risk->rcsaWorkspace)
                                Last updated {{ optional($risk->rcsaWorkspace->updated_at)->format('d-m-Y H:i') ?? '-' }}
                                @if($risk->rcsaWorkspace->updater)
                                    by {{ $risk->rcsaWorkspace->updater->name }}
                                @endif
                            @else
                                No RCSA assessment captured yet.
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('risks.rcsa.show', $risk->id) }}" class="btn btn-sm btn-outline-primary">Open Full Workspace</a>
                </div>

                @if($risk->rcsaWorkspace)
                    <div class="row g-3">
                        <div class="col-md-4"><strong>Process Owner</strong><div>{{ $risk->rcsaWorkspace->process_owner ?: '-' }}</div></div>
                        <div class="col-md-4"><strong>Review Frequency</strong><div>{{ $risk->rcsaWorkspace->review_frequency ?: '-' }}</div></div>
                        <div class="col-md-4"><strong>Next Review</strong><div>{{ optional($risk->rcsaWorkspace->next_review_at)->format('d-m-Y') ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Objective</strong><div>{!! nl2br(e($risk->rcsaWorkspace->objective ?: '-')) !!}</div></div>
                        <div class="col-md-6"><strong>Scope</strong><div>{!! nl2br(e($risk->rcsaWorkspace->scope ?: '-')) !!}</div></div>
                        <div class="col-md-6"><strong>Key Controls</strong><div>{!! nl2br(e($risk->rcsaWorkspace->key_controls ?: '-')) !!}</div></div>
                        <div class="col-md-6"><strong>Control Gaps</strong><div>{!! nl2br(e($risk->rcsaWorkspace->control_gaps ?: '-')) !!}</div></div>
                        <div class="col-md-6"><strong>Likelihood Rationale</strong><div>{!! nl2br(e($risk->rcsaWorkspace->likelihood_rationale ?: '-')) !!}</div></div>
                        <div class="col-md-6"><strong>Impact Rationale</strong><div>{!! nl2br(e($risk->rcsaWorkspace->impact_rationale ?: '-')) !!}</div></div>
                        <div class="col-md-4"><strong>Sign-Off Name</strong><div>{{ $risk->rcsaWorkspace->sign_off_name ?: '-' }}</div></div>
                        <div class="col-md-4"><strong>Sign-Off Role</strong><div>{{ $risk->rcsaWorkspace->sign_off_role ?: '-' }}</div></div>
                        <div class="col-md-4"><strong>Signed-Off Date</strong><div>{{ optional($risk->rcsaWorkspace->signed_off_at)->format('d-m-Y') ?? '-' }}</div></div>
                        <div class="col-md-12"><strong>Notes</strong><div>{!! nl2br(e($risk->rcsaWorkspace->notes ?: '-')) !!}</div></div>
                    </div>
                @else
                    <div class="text-muted">No RCSA workspace data available.</div>
                @endif
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="residual">
        <div class="card">
            <div class="card-body">
                @if($risk->residualAnalysis)
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Probability Description</strong><div>{{ $risk->residualAnalysis->probability_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Probability Score</strong><div>{{ $risk->residualAnalysis->probability_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Impact Description</strong><div>{{ $risk->residualAnalysis->impact_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Impact Score</strong><div>{{ $risk->residualAnalysis->impact_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Residual Risk Score</strong><div>{{ $risk->residualAnalysis->residual_risk_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Risk Level</strong><div>{{ $risk->residualAnalysis->risk_level ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Risk Adjustment</strong><div>{{ $risk->residualAnalysis->risk_adjustment ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Overall Adjustment</strong><div>{{ $risk->residualAnalysis->adjustment ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Level Adjustment</strong><div>{{ $risk->residualAnalysis->risk_level_adjustment ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Radar Description</strong><div>{{ optional($risk->residualAnalysis->radarGroup)->description ?? $risk->residualAnalysis->radar_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Radar Code</strong><div>{{ optional($risk->residualAnalysis->radarGroup)->code ?? $risk->residualAnalysis->radar_code ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Residual Impact (Rp)</strong><div>{{ is_null($risk->residualAnalysis->impact_amount) ? '-' : number_format((float) $risk->residualAnalysis->impact_amount, 0, ',', '.') }}</div></div>
                        <div class="col-md-6"><strong>Residual Probability (%)</strong><div>{{ is_null($risk->residualAnalysis->probability_percent) ? '-' : number_format(((float) $risk->residualAnalysis->probability_percent) * 100, 2, '.', '') . '%' }}</div></div>
                        <div class="col-md-6"><strong>Residual Potential Loss (Rp)</strong><div>{{ is_null($risk->residualAnalysis->potential_loss) ? '-' : number_format((float) $risk->residualAnalysis->potential_loss, 0, ',', '.') }}</div></div>
                    </div>
                @else
                    <div class="text-muted">No residual analysis data.</div>
                @endif
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="bcm">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>Business Impact Analysis (BIA)</strong>
                    </div>
                    <div class="card-body">
                        @php
                            $bia = $risk->bia ?? new \App\Bia();
                        @endphp
                        <form action="{{ route('bia.store', $risk->id) }}" method="POST">
                            @csrf
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Recovery Time Objective (RTO)</label>
                                    <select name="rto" class="form-select form-select-sm">
                                        <option value="">Select RTO</option>
                                        @foreach(['1 Hour', '4 Hours', '24 Hours', '1 Week', '1 Month'] as $opt)
                                            <option value="{{ $opt }}" {{ $bia->rto === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Recovery Point Objective (RPO)</label>
                                    <select name="rpo" class="form-select form-select-sm">
                                        <option value="">Select RPO</option>
                                        @foreach(['1 Hour', '4 Hours', '24 Hours', '1 Week', '1 Month'] as $opt)
                                            <option value="{{ $opt }}" {{ $bia->rpo === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Max Acceptable Outage</label>
                                    <select name="mao" class="form-select form-select-sm">
                                        <option value="">Select MAO</option>
                                        @foreach(['1 Hour', '4 Hours', '24 Hours', '1 Week', '1 Month'] as $opt)
                                            <option value="{{ $opt }}" {{ $bia->mao === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                @php $impacts = ['impact_financial' => 'Financial', 'impact_operational' => 'Operational', 'impact_regulatory' => 'Regulatory', 'impact_reputational' => 'Reputational']; @endphp
                                @foreach($impacts as $field => $label)
                                    <div class="col-md-6">
                                        <label class="form-label">{{ $label }} Impact</label>
                                        <select name="{{ $field }}" class="form-select form-select-sm">
                                            <option value="">Select Impact</option>
                                            @foreach(['Low', 'Medium', 'High', 'Critical'] as $opt)
                                                <option value="{{ $opt }}" {{ $bia->$field === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Criticality Tier</label>
                                <select name="criticality_tier" class="form-select form-select-sm">
                                    <option value="">Select Tier</option>
                                    @foreach(['Tier 1', 'Tier 2', 'Tier 3', 'Tier 4'] as $opt)
                                        <option value="{{ $opt }}" {{ $bia->criticality_tier === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Justification / Notes</label>
                                <textarea name="justification_notes" class="form-control form-control-sm" rows="3">{{ $bia->justification_notes }}</textarea>
                            </div>

                            @if($canManage)
                                <button type="submit" class="btn btn-sm btn-primary">Save BIA</button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 {{ !$risk->bia ? 'bg-secondary-lt' : '' }}">
                    <div class="card-header">
                        <strong>Business Continuity Plan (BCP)</strong>
                    </div>
                    <div class="card-body">
                        @if($risk->bia)
                            @php
                                $bcp = $risk->bia->bcp ?? new \App\Bcp();
                            @endphp
                            <form action="{{ route('bcp.store', $risk->bia->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Recovery Strategy Summary</label>
                                    <textarea name="strategy_summary" class="form-control form-control-sm" rows="2">{{ $bcp->strategy_summary }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Recovery Step-by-Step Procedures</label>
                                    <textarea name="recovery_procedures" class="form-control form-control-sm" rows="3">{{ $bcp->recovery_procedures }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Primary Workaround</label>
                                    <textarea name="primary_workaround" class="form-control form-control-sm" rows="2">{{ $bcp->primary_workaround }}</textarea>
                                </div>

                                <hr>
                                <strong>Testing & Maintenance</strong>
                                
                                <div class="row g-2 mt-2 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Testing Frequency</label>
                                        <select name="testing_frequency" class="form-select form-select-sm">
                                            <option value="">Select Frequency</option>
                                            @foreach(['Monthly', 'Quarterly', 'Semi-Annual', 'Annual'] as $opt)
                                                <option value="{{ $opt }}" {{ $bcp->testing_frequency === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Last Tested Date</label>
                                        <input type="date" name="last_tested_at" class="form-control form-control-sm" value="{{ optional($bcp->last_tested_at)->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Last Test Result</label>
                                        <select name="last_test_result" class="form-select form-select-sm">
                                            <option value="">Select Result</option>
                                            @foreach(['Pass', 'Partial', 'Fail'] as $opt)
                                                <option value="{{ $opt }}" {{ $bcp->last_test_result === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @if($canManage)
                                    <button type="submit" class="btn btn-sm btn-primary">Save BCP</button>
                                @endif
                            </form>
                        @else
                            <div class="text-muted d-flex align-items-center justify-content-center h-100">
                                <div>Please complete and save the <strong>Business Impact Analysis (BIA)</strong> first to unlock the BCP form.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="governance">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Risk Evidence</strong>
            </div>
            <div class="card-body">
                @if($canManage)
                    <form class="d-flex gap-2 mb-3" action="{{ route('risks.attachments.store', $risk->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" class="form-control form-control-sm" required>
                        <input type="text" name="notes" class="form-control form-control-sm" maxlength="1000" placeholder="Notes">
                        <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                    </form>
                @endif
                @forelse($risk->attachments as $attachment)
                    <div class="d-flex justify-content-between align-items-center border rounded px-2 py-1 mb-1">
                        <div>
                            <strong>{{ $attachment->original_name }}</strong>
                            <div class="text-muted small">
                                {{ optional($attachment->uploader)->name ?? 'System' }}
                                - {{ optional($attachment->created_at)->format('d-m-Y H:i') ?? '-' }}
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                            @if($canManage)
                                <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this attachment?')">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted">No risk evidence.</div>
                @endforelse
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Discussion Thread</strong>
                <span class="badge bg-blue-lt">{{ $risk->comments->count() }} comments</span>
            </div>
            <div class="card-body">
                @if($canComment)
                    <form class="mb-3" action="{{ route('risks.comments.store', $risk->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Comment</label>
                            <div class="mention-field-wrap">
                                <textarea name="content" class="form-control form-control-sm mention-input" rows="3" maxlength="5000" placeholder="Type your comment. Use @username to mention." required>{{ old('content') }}</textarea>
                                <div class="mention-highlight-layer" aria-hidden="true"></div>
                                <div class="mention-suggestion-list" aria-hidden="true"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Attachments (optional)</label>
                            <input type="file" name="attachments[]" class="form-control form-control-sm" multiple>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Post Comment</button>
                    </form>
                @else
                    <div class="alert alert-warning py-2">
                        You do not have permission to post comments on this risk.
                    </div>
                @endif

                <div class="mt-2">
                    @forelse($risk->comments as $comment)
                        @php
                            $rawContent = (string) ($comment->content ?? '');
                            $escapedContent = e($rawContent);
                            $contentWithMentions = preg_replace(
                                '/@([A-Za-z0-9_.-]+)/',
                                '<span class="badge bg-blue-lt">@$1</span>',
                                $escapedContent
                            );
                            $canManageComment = $canComment
                                && (int) ($comment->user_id ?? 0) === (int) $currentUser->id;
                        @endphp
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <div class="fw-bold">{{ optional($comment->user)->name ?? 'Unknown User' }}</div>
                                    <div class="text-muted small">
                                        {{ '@' . (optional($comment->user)->username ?? '-') }}
                                        · {{ optional($comment->created_at)->format('d-m-Y H:i') ?? '-' }}
                                        @if(!is_null($comment->edited_at))
                                            · edited {{ optional($comment->edited_at)->format('d-m-Y H:i') }}
                                        @endif
                                    </div>
                                </div>
                                @if($canManageComment)
                                    <div class="d-flex gap-1">
                                        <details>
                                            <summary class="btn btn-sm btn-outline-warning">Edit</summary>
                                            <form class="mt-2" action="{{ route('risks.comments.update', [$risk->id, $comment->id]) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="mention-field-wrap mb-2">
                                                    <textarea name="content" class="form-control form-control-sm mention-input" rows="3" maxlength="5000" required>{{ $comment->content }}</textarea>
                                                    <div class="mention-highlight-layer" aria-hidden="true"></div>
                                                    <div class="mention-suggestion-list" aria-hidden="true"></div>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-warning">Save</button>
                                            </form>
                                        </details>
                                        <form action="{{ route('risks.comments.destroy', [$risk->id, $comment->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this comment?')">Delete</button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-2">{!! nl2br($contentWithMentions) !!}</div>

                            @if($comment->mentions->isNotEmpty())
                                <div class="small text-muted mt-2">
                                    Mentioned:
                                    {{ $comment->mentions->pluck('username')->map(function ($username) { return '@' . $username; })->implode(', ') }}
                                </div>
                            @endif

                            <div class="mt-2">
                                @forelse($comment->attachments as $attachment)
                                    <div class="d-flex justify-content-between align-items-center border rounded px-2 py-1 mb-1">
                                        <div>
                                            <strong>{{ $attachment->original_name }}</strong>
                                            <div class="text-muted small">
                                                {{ optional($attachment->uploader)->name ?? 'System' }}
                                                · {{ optional($attachment->created_at)->format('d-m-Y H:i') ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                            @if($canManageComment)
                                                <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this attachment?')">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted small">No comment attachments.</div>
                                @endforelse
                            </div>

                            @if($canManageComment)
                                <form class="d-flex gap-2 mt-2" action="{{ route('risks.comments.attachments.store', [$risk->id, $comment->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="file" class="form-control form-control-sm" required>
                                    <input type="text" name="notes" class="form-control form-control-sm" maxlength="1000" placeholder="Attachment notes (optional)">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Attach</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">No comments yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Activity Feed</strong>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Actor</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activityLogs as $activity)
                            <tr>
                                <td>{{ $activity['time'] }}</td>
                                <td>{{ $activity['actor'] }}</td>
                                <td>{{ $activity['event_label'] }}</td>
                                <td>{{ $activity['entity'] }}</td>
                                <td>{{ $activity['details'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No activity logs for this risk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Status Approval History</strong>
                @if($isSuperadmin)
                    <a href="{{ route('risk-status-approvals.index') }}" class="btn btn-sm btn-outline-primary">Approval Queue</a>
                @endif
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Requested At</th>
                            <th>Requester</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($risk->statusChangeRequests as $statusRequest)
                            <tr>
                                <td>{{ optional($statusRequest->created_at)->format('d-m-Y H:i') ?? '-' }}</td>
                                <td>{{ optional($statusRequest->requester)->name ?? '-' }}</td>
                                <td>{{ $statusRequest->from_status }}</td>
                                <td>{{ $statusRequest->to_status }}</td>
                                <td>{{ strtoupper($statusRequest->status) }}</td>
                                <td>{{ $statusRequest->reason ?: '-' }}</td>
                                <td>
                                    @if($statusRequest->status === 'pending')
                                        -
                                    @else
                                        {{ optional($statusRequest->reviewer)->name ?? '-' }}
                                        ({{ optional($statusRequest->reviewed_at)->format('d-m-Y H:i') ?? '-' }})
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No status change requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Risk Alerts</strong>
                @if($isSuperadmin)
                    <a href="{{ route('risk-alerts.index') }}" class="btn btn-sm btn-outline-primary">Alert Center</a>
                @endif
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Message</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($risk->alerts as $alert)
                            <tr>
                                <td>{{ $alert->alert_type_label }}</td>
                                <td>{{ strtoupper($alert->severity) }}</td>
                                <td>{{ strtoupper($alert->status) }}</td>
                                <td>{{ $alert->due_date ? \Carbon\Carbon::parse($alert->due_date)->format('d-m-Y') : '-' }}</td>
                                <td>{{ $alert->message }}</td>
                                <td>{{ optional($alert->created_at)->format('d-m-Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No alerts.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="kris">
        @include('kris._list')
    </div>
</div>
@stop

