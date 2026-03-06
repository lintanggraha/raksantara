@once
    <style>
        .risk-page-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.95rem;
        }

        .risk-page-tab {
            display: block;
            font: inherit;
            cursor: pointer;
            border: 1px solid #d1d9e6;
            border-radius: 0.6rem;
            background: #ffffff;
            color: #334155;
            padding: 0.45rem 0.75rem;
            min-width: 230px;
            text-align: left;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .risk-page-tab-title {
            display: block;
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .risk-page-tab-subtitle {
            display: block;
            font-size: 0.86rem;
            font-weight: 600;
            color: #0f172a;
        }

        .risk-page-tab.active {
            border-color: #90a7c5;
            background: linear-gradient(145deg, #ecf3ff 0%, #f8fbff 100%);
            color: #0f172a;
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.12);
        }

        .risk-page-tab:hover {
            border-color: #b9c7da;
            text-decoration: none;
            color: #0f172a;
        }

        @media (max-width: 768px) {
            .risk-page-tab {
                min-width: calc(50% - 0.25rem);
            }
        }

        @media (max-width: 560px) {
            .risk-page-tab {
                min-width: 100%;
            }
        }
    </style>
@endonce

@php
    $workspaceMode = (bool) ($workspaceMode ?? false);
    $activeRiskTab = (string) ($activeRiskTab ?? (request()->query('risk_tab', 'register')));
    if (!in_array($activeRiskTab, ['register', 'heatmap', 'recap'], true)) {
        $activeRiskTab = 'register';
    }
@endphp

<nav class="risk-page-tabs" role="tablist" aria-label="Risk Pages">
    @if($workspaceMode)
        <button
            type="button"
            class="risk-page-tab {{ $activeRiskTab === 'register' ? 'active' : '' }}"
            data-risk-workspace-tab="register"
            role="tab"
            aria-selected="{{ $activeRiskTab === 'register' ? 'true' : 'false' }}"
        >
            <span class="risk-page-tab-title">Dashboard 1</span>
            <span class="risk-page-tab-subtitle">Risk Register</span>
        </button>
        <button
            type="button"
            class="risk-page-tab {{ $activeRiskTab === 'heatmap' ? 'active' : '' }}"
            data-risk-workspace-tab="heatmap"
            role="tab"
            aria-selected="{{ $activeRiskTab === 'heatmap' ? 'true' : 'false' }}"
        >
            <span class="risk-page-tab-title">Dashboard 2</span>
            <span class="risk-page-tab-subtitle">Risk Heat Map</span>
        </button>
        <button
            type="button"
            class="risk-page-tab {{ $activeRiskTab === 'recap' ? 'active' : '' }}"
            data-risk-workspace-tab="recap"
            role="tab"
            aria-selected="{{ $activeRiskTab === 'recap' ? 'true' : 'false' }}"
        >
            <span class="risk-page-tab-title">Dashboard 3</span>
            <span class="risk-page-tab-subtitle">Risk Recapitulation</span>
        </button>
    @else
        <a
            href="{{ route('risks.index', ['risk_tab' => 'register']) }}"
            class="risk-page-tab {{ $activeRiskTab === 'register' ? 'active' : '' }}"
            role="tab"
            aria-selected="{{ $activeRiskTab === 'register' ? 'true' : 'false' }}"
        >
            <span class="risk-page-tab-title">Dashboard 1</span>
            <span class="risk-page-tab-subtitle">Risk Register</span>
        </a>
        <a
            href="{{ route('risks.index', ['risk_tab' => 'heatmap']) }}"
            class="risk-page-tab {{ $activeRiskTab === 'heatmap' ? 'active' : '' }}"
            role="tab"
            aria-selected="{{ $activeRiskTab === 'heatmap' ? 'true' : 'false' }}"
        >
            <span class="risk-page-tab-title">Dashboard 2</span>
            <span class="risk-page-tab-subtitle">Risk Heat Map</span>
        </a>
        <a
            href="{{ route('risks.index', ['risk_tab' => 'recap']) }}"
            class="risk-page-tab {{ $activeRiskTab === 'recap' ? 'active' : '' }}"
            role="tab"
            aria-selected="{{ $activeRiskTab === 'recap' ? 'true' : 'false' }}"
        >
            <span class="risk-page-tab-title">Dashboard 3</span>
            <span class="risk-page-tab-subtitle">Risk Recapitulation</span>
        </a>
    @endif
</nav>
