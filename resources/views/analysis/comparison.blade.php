@extends('layouts.tabler')

@section('title', 'Komparasi Framework & Gap Analysis - Raksantara')

@push('styles')
<style>
.comp-page {
    --ink: #0f172a;
    --muted: #475569;
    --line: rgba(15, 23, 42, 0.1);
    --shell: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    color: var(--ink);
    padding: .25rem;
    border-radius: 32px;
    background: var(--shell);
}
.comp-shell { display: grid; gap: 1rem; }
.comp-hero {
    display: grid; grid-template-columns: 1.6fr .9fr; gap: 1.25rem;
    align-items: stretch; min-height: 280px;
    padding: 1.45rem; border-radius: 28px; overflow: hidden; position: relative;
    background:
        radial-gradient(circle at top right, rgba(187, 247, 208, .85), transparent 32%),
        radial-gradient(circle at bottom left, rgba(220, 252, 231, .6), transparent 28%),
        linear-gradient(135deg, #064e3b 0%, #047857 45%, #059669 100%);
    box-shadow: 0 20px 44px rgba(6, 78, 59, .15);
}
.comp-hero > * { position: relative; z-index: 1; }
.comp-kicker {
    display: inline-flex; align-items: center; gap: .45rem; padding: .35rem .7rem;
    border-radius: 999px; background: rgba(255, 255, 255, .18); color: #ecfdf5;
    font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
}
.comp-title { margin: 1rem 0 .7rem; color: #ffffff; font-size: clamp(2rem, 3.8vw, 2.75rem); font-weight: 800; line-height: 1.05; }
.comp-lede { margin: 0; max-width: 680px; color: rgba(240, 253, 250, .9); line-height: 1.65; font-size: .95rem; }

.comp-panel {
    padding: 1.15rem; border-radius: 20px; border: 1px solid var(--line);
    background: #ffffff; box-shadow: 0 14px 28px rgba(15, 23, 42, .04);
}
.comp-panel-head { display: flex; align-items: center; justify-content: space-between; gap: .85rem; margin-bottom: .85rem; }
.comp-panel-head h3 { margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; }

.comp-nav { display: grid; grid-template-columns: repeat(2, 1fr); gap: .85rem; }
.comp-tab {
    display: grid; grid-template-columns: auto 1fr; gap: .85rem; align-items: center;
    padding: .82rem .88rem; border-radius: 18px; border: 1px solid var(--line);
    background: linear-gradient(180deg, rgba(255, 255, 255, .95) 0%, rgba(240, 253, 250, .95) 100%);
    text-align: left; color: var(--ink); box-shadow: 0 12px 24px rgba(15, 23, 42, .04); cursor: pointer;
    transition: all .18s ease;
}
.comp-tab.active { border-color: rgba(5, 150, 105, .4); box-shadow: 0 16px 30px rgba(5, 150, 105, .15); background: #ffffff; }
.comp-tab i {
    width: 2.35rem; height: 2.35rem; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 14px; background: rgba(5, 150, 105, .1); color: #059669;
}
.comp-tab strong { display: block; font-size: .9rem; font-weight: 800; }
.comp-tab span { display: block; margin-top: .14rem; color: var(--muted); font-size: .76rem; line-height: 1.4; }

.gap-stat-card {
    border-radius: 16px; padding: 1rem; text-align: center; border: 2px solid transparent;
    cursor: pointer; transition: all .15s ease; background: #ffffff;
}
.gap-stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(15, 23, 42, .06); }
.gap-stat-card.active { border-color: #059669; background: #f0fdf4; }
.gap-stat-card strong { display: block; font-size: 1.75rem; font-weight: 800; line-height: 1; }
.gap-stat-card span { display: block; margin-top: .35rem; font-size: .76rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); }

.status-badge {
    display: inline-flex; align-items: center; gap: .35rem; padding: .35rem .65rem;
    border-radius: 8px; font-size: .75rem; font-weight: 700;
}
.status-badge.covered { background: #dcfce7; color: #166534; }
.status-badge.partial { background: #fef3c7; color: #92400e; }
.status-badge.gap { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')
@php
    $fwList = $comparisonData['frameworks'] ?? [];
    $topicList = $comparisonData['topics'] ?? [];
    $compMappings = $comparisonData['mappings'] ?? [];
    $gapControls = $gapData['controls'] ?? [];
    $gapMappings = $gapData['mappings'] ?? [];
@endphp

<div class="comp-page">
    <div class="comp-shell">
        <!-- Hero -->
        <div class="comp-hero">
            <div>
                <span class="comp-kicker"><i class="fas fa-arrows-split-up-and-left me-1"></i> Analisis Kepatuhan • Komparasi Dinamis</span>
                <h1 class="comp-title">Komparasi & Gap Analysis</h1>
                <p class="comp-lede">
                    Lakukan perbandingan langsung dua framework keamanan (misalnya ISO 27001 vs NIST CSF atau POJK 11 vs PADK 2026), identifikasi celah kepatuhan (gap), dan tentukan prioritas tindakan remediasi dengan analisis terukur.
                </p>
            </div>
            <div style="background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .25); border-radius: 20px; padding: 1.25rem; backdrop-filter: blur(8px); display: flex; flex-direction: column; justify-content: center;">
                <label style="font-size: .72rem; font-weight: 700; color: #a7f3d0; text-transform: uppercase;">Manfaat Komparasi</label>
                <h3 style="color: #ffffff; font-size: 1.1rem; margin: .4rem 0 .6rem;">Evaluasi Kesiapan Regulasi</h3>
                <p style="color: rgba(255, 255, 255, .82); font-size: .84rem; line-height: 1.5; margin: 0;">
                    Ketahui sejauh mana kontrol eksisting organisasi Anda sudah memenuhi regulasi baru tanpa harus memulai penilaian dari nol.
                </p>
            </div>
        </div>

        <!-- Mode Tabs -->
        <div class="comp-nav">
            <button class="comp-tab active" data-tab="tab-side-by-side">
                <i class="fas fa-table-columns"></i>
                <div>
                    <strong>Komparasi Berdampingan</strong>
                    <span>Bandingkan pasal & klausul antar 2 framework</span>
                </div>
            </button>
            <button class="comp-tab" data-tab="tab-gap-analysis">
                <i class="fas fa-chart-gantt"></i>
                <div>
                    <strong>Gap Analysis Komprehensif</strong>
                    <span>Persentase cakupan & daftar kesenjangan (gap)</span>
                </div>
            </button>
        </div>

        <!-- Global Framework Selectors -->
        <div class="comp-panel">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-muted small text-uppercase">Framework A (Baseline)</label>
                    <select id="fw-a-select" class="form-select form-select-lg">
                        @foreach($fwList as $fw)
                            <option value="{{ $fw['id'] }}" {{ $fw['id'] === $defaultFrameworkA ? 'selected' : '' }}>
                                {{ $fw['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-center d-none d-md-block">
                    <span class="badge bg-light text-muted border p-2 mt-4"><i class="fas fa-arrow-right-arrow-left"></i> VS</span>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold text-muted small text-uppercase">Framework B (Target / Pembanding)</label>
                    <select id="fw-b-select" class="form-select form-select-lg">
                        @foreach($fwList as $fw)
                            <option value="{{ $fw['id'] }}" {{ $fw['id'] === $defaultFrameworkB ? 'selected' : '' }}>
                                {{ $fw['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- TAB 1: SIDE BY SIDE -->
        <div class="comp-tab-content" id="tab-side-by-side">
            <!-- Topic Filter -->
            <div class="comp-panel mb-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="fw-bold small text-muted text-uppercase me-2">Filter Topik:</span>
                        <button class="btn btn-sm btn-primary rounded-pill topic-filter-btn" data-topic="all">Semua Topik</button>
                        @foreach($topicList as $topic)
                            <button class="btn btn-sm btn-outline-secondary rounded-pill topic-filter-btn" data-topic="{{ $topic['id'] }}">
                                {{ $topic['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="comp-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-top">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50%;" class="p-3 text-center fw-bold fs-5 text-primary" id="th-fw-a">
                                    {{ collect($fwList)->firstWhere('id', $defaultFrameworkA)['name'] ?? 'Framework A' }}
                                </th>
                                <th style="width: 50%;" class="p-3 text-center fw-bold fs-5 text-success" id="th-fw-b">
                                    {{ collect($fwList)->firstWhere('id', $defaultFrameworkB)['name'] ?? 'Framework B' }}
                                </th>
                            </tr>
                        </thead>
                        <tbody id="side-by-side-tbody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: GAP ANALYSIS -->
        <div class="comp-tab-content d-none" id="tab-gap-analysis">
            <!-- Stats Bar -->
            <div class="comp-panel mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-primary"></i> Rasio Ketercakupan Gap (Coverage Ratio)</h4>
                    <button class="btn btn-sm btn-outline-success" id="btn-export-gap-csv">
                        <i class="fas fa-file-csv me-1"></i> Ekspor CSV
                    </button>
                </div>

                <div class="progress mb-3 rounded-pill" style="height: 28px; font-weight: 800; font-size: .85rem;">
                    <div class="progress-bar bg-success" id="gap-bar-covered" style="width: 0%;">0%</div>
                    <div class="progress-bar bg-warning text-dark" id="gap-bar-partial" style="width: 0%;">0%</div>
                    <div class="progress-bar bg-danger" id="gap-bar-gap" style="width: 0%;">0%</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="gap-stat-card border border-success" data-filter="Covered">
                            <strong class="text-success" id="stat-count-covered">0</strong>
                            <span><i class="fas fa-check-circle text-success me-1"></i> Terpenuhi Penuh</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="gap-stat-card border border-warning" data-filter="Partial">
                            <strong class="text-warning" id="stat-count-partial">0</strong>
                            <span><i class="fas fa-triangle-exclamation text-warning me-1"></i> Parsial</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="gap-stat-card border border-danger" data-filter="Gap">
                            <strong class="text-danger" id="stat-count-gap">0</strong>
                            <span><i class="fas fa-circle-xmark text-danger me-1"></i> Celah Regulasi (Gap)</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="gap-stat-card border border-secondary active" data-filter="All">
                            <strong class="text-dark" id="stat-count-total">0</strong>
                            <span><i class="fas fa-list me-1"></i> Total Persyaratan</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Gap Table -->
            <div class="comp-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 align-top">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 38%;" class="p-3" id="th-gap-baseline">Persyaratan Baseline</th>
                                <th style="width: 16%;" class="p-3 text-center">Status Cakupan</th>
                                <th style="width: 46%;" class="p-3" id="th-gap-target">Rincian Kontrol Pembanding & Solusi</th>
                            </tr>
                        </thead>
                        <tbody id="gap-analysis-tbody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
const compData = @json($comparisonData);
const gapData = @json($gapData);

let currentTopic = 'all';
let currentGapFilter = 'All';

document.addEventListener('DOMContentLoaded', function () {
    // Tab switching
    const tabs = document.querySelectorAll('.comp-tab');
    const contents = document.querySelectorAll('.comp-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.add('d-none'));

            tab.classList.add('active');
            const targetId = tab.getAttribute('data-tab');
            document.getElementById(targetId)?.classList.remove('d-none');
        });
    });

    const selectA = document.getElementById('fw-a-select');
    const selectB = document.getElementById('fw-b-select');

    function getFwName(id) {
        const found = (compData.frameworks || []).find(f => f.id === id);
        return found ? found.name : id;
    }

    function renderSideBySide() {
        const fwA = selectA.value;
        const fwB = selectB.value;

        document.getElementById('th-fw-a').textContent = getFwName(fwA);
        document.getElementById('th-fw-b').textContent = getFwName(fwB);

        const tbody = document.getElementById('side-by-side-tbody');
        tbody.innerHTML = '';

        const topics = compData.topics || [];
        const mappings = compData.mappings || [];

        let rowsAdded = 0;

        topics.forEach(topic => {
            if (currentTopic !== 'all' && currentTopic !== topic.id) return;

            const topicRows = mappings.filter(m => m.topic === topic.id);
            if (topicRows.length === 0) return;

            // Topic header row
            const trHeader = document.createElement('tr');
            trHeader.innerHTML = `
                <td colspan="2" class="bg-light fw-bold text-center py-2 text-uppercase text-muted" style="letter-spacing: .08em; font-size: .8rem;">
                    <i class="fas fa-layer-group me-1"></i> ${topic.name}
                </td>
            `;
            tbody.appendChild(trHeader);

            topicRows.forEach(row => {
                const cellA = row[fwA];
                const cellB = row[fwB];

                const tr = document.createElement('tr');

                const tdA = document.createElement('td');
                tdA.className = 'p-3';
                if (cellA) {
                    tdA.innerHTML = `
                        <span class="badge bg-secondary mb-1">${cellA.id || ''}</span>
                        <p class="mb-0 text-dark" style="font-size: .88rem; line-height: 1.5;">${cellA.desc || ''}</p>
                    `;
                } else {
                    tdA.innerHTML = `<span class="text-muted small fst-italic"><i class="fas fa-minus me-1"></i> Tidak memiliki kontrol padanan eksplisit</span>`;
                }

                const tdB = document.createElement('td');
                tdB.className = 'p-3';
                if (cellB) {
                    tdB.innerHTML = `
                        <span class="badge bg-secondary mb-1">${cellB.id || ''}</span>
                        <p class="mb-0 text-dark" style="font-size: .88rem; line-height: 1.5;">${cellB.desc || ''}</p>
                    `;
                } else {
                    tdB.innerHTML = `<span class="text-muted small fst-italic"><i class="fas fa-minus me-1"></i> Tidak memiliki kontrol padanan eksplisit</span>`;
                }

                tr.appendChild(tdA);
                tr.appendChild(tdB);
                tbody.appendChild(tr);
                rowsAdded++;
            });
        });

        if (rowsAdded === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="2" class="text-center p-5 text-muted">
                        <i class="fas fa-inbox fs-1 mb-2 d-block"></i>
                        Tidak ada data komparasi topik untuk pasangan framework ini.
                    </td>
                </tr>
            `;
        }
    }

    function renderGapAnalysis() {
        const fwA = selectA.value;
        const fwB = selectB.value;

        document.getElementById('th-gap-baseline').textContent = `Persyaratan Baseline (${getFwName(fwA)})`;
        document.getElementById('th-gap-target').textContent = `Rincian Kontrol Pembanding (${getFwName(fwB)}) & Remediasi`;

        const controls = gapData.controls || [];
        const mappings = gapData.mappings || {};

        let covered = 0;
        let partial = 0;
        let gap = 0;

        const results = controls.map(ctrl => {
            const mapA = mappings[fwA] ? mappings[fwA][ctrl.id] : null;
            const mapB = mappings[fwB] ? mappings[fwB][ctrl.id] : null;

            // Determine status
            let status = 'Gap';
            if (mapB && mapB.status === 'C') {
                status = 'Covered';
                covered++;
            } else if (mapB && mapB.status === 'P') {
                status = 'Partial';
                partial++;
            } else {
                gap++;
            }

            return {
                req: ctrl,
                status: status,
                sourceDetail: mapA ? mapA.detail : '—',
                targetDetail: mapB ? mapB.detail : 'Tidak ada persyaratan padanan langsung.',
                recommendation: status === 'Gap' 
                    ? 'Implementasikan kontrol baru untuk menutup celah regulasi ini.'
                    : (status === 'Partial' ? 'Perluas cakupan kontrol agar selaras penuh dengan klausul pembanding.' : 'Kontrol telah terpenuhi dengan baik.')
            };
        });

        const total = controls.length || 1;
        const coveredPct = Math.round((covered / total) * 100);
        const partialPct = Math.round((partial / total) * 100);
        const gapPct = Math.round((gap / total) * 100);

        document.getElementById('gap-bar-covered').style.width = coveredPct + '%';
        document.getElementById('gap-bar-covered').textContent = coveredPct > 0 ? coveredPct + '%' : '';
        document.getElementById('gap-bar-partial').style.width = partialPct + '%';
        document.getElementById('gap-bar-partial').textContent = partialPct > 0 ? partialPct + '%' : '';
        document.getElementById('gap-bar-gap').style.width = gapPct + '%';
        document.getElementById('gap-bar-gap').textContent = gapPct > 0 ? gapPct + '%' : '';

        document.getElementById('stat-count-covered').textContent = covered;
        document.getElementById('stat-count-partial').textContent = partial;
        document.getElementById('stat-count-gap').textContent = gap;
        document.getElementById('stat-count-total').textContent = controls.length;

        const tbody = document.getElementById('gap-analysis-tbody');
        tbody.innerHTML = '';

        results.forEach(item => {
            if (currentGapFilter !== 'All' && item.status !== currentGapFilter) return;

            const tr = document.createElement('tr');

            let badgeHtml = '';
            if (item.status === 'Covered') badgeHtml = '<span class="status-badge covered"><i class="fas fa-check-circle"></i> Terpenuhi</span>';
            else if (item.status === 'Partial') badgeHtml = '<span class="status-badge partial"><i class="fas fa-triangle-exclamation"></i> Parsial</span>';
            else badgeHtml = '<span class="status-badge gap"><i class="fas fa-circle-xmark"></i> Kesenjangan (Gap)</span>';

            tr.innerHTML = `
                <td class="p-3">
                    <strong class="d-block text-dark">${item.req.name}</strong>
                    <p class="mb-1 text-muted small">${item.req.desc}</p>
                    <small class="text-secondary">${item.sourceDetail}</small>
                </td>
                <td class="p-3 text-center">${badgeHtml}</td>
                <td class="p-3">
                    <p class="mb-1 text-dark" style="font-size: .88rem;">${item.targetDetail}</p>
                    <small class="text-primary fw-bold"><i class="fas fa-lightbulb me-1"></i> ${item.recommendation}</small>
                </td>
            `;

            tbody.appendChild(tr);
        });
    }

    // Event listeners
    selectA.addEventListener('change', () => { renderSideBySide(); renderGapAnalysis(); });
    selectB.addEventListener('change', () => { renderSideBySide(); renderGapAnalysis(); });

    document.querySelectorAll('.topic-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.topic-filter-btn').forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline-secondary');
            });
            btn.classList.add('btn-primary');
            btn.classList.remove('btn-outline-secondary');
            currentTopic = btn.dataset.topic;
            renderSideBySide();
        });
    });

    document.querySelectorAll('.gap-stat-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.gap-stat-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            currentGapFilter = card.dataset.filter;
            renderGapAnalysis();
        });
    });

    // CSV Export
    document.getElementById('btn-export-gap-csv')?.addEventListener('click', () => {
        const fwA = selectA.value;
        const fwB = selectB.value;
        const rows = [
            ['Baseline Requirement', 'Baseline Detail', 'Status', 'Target Detail', 'Recommendation']
        ];

        const controls = gapData.controls || [];
        const mappings = gapData.mappings || {};

        controls.forEach(ctrl => {
            const mapA = mappings[fwA] ? mappings[fwA][ctrl.id] : null;
            const mapB = mappings[fwB] ? mappings[fwB][ctrl.id] : null;
            const status = mapB ? (mapB.status === 'C' ? 'Covered' : (mapB.status === 'P' ? 'Partial' : 'Gap')) : 'Gap';
            rows.push([
                ctrl.name,
                mapA ? mapA.detail : '',
                status,
                mapB ? mapB.detail : '',
                status === 'Gap' ? 'Need Implementation' : (status === 'Partial' ? 'Expand scope' : 'Compliant')
            ]);
        });

        let csvContent = 'data:text/csv;charset=utf-8,' + rows.map(e => e.map(i => `"${(i||'').replace(/"/g, '""')}"`).join(',')).join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `gap_analysis_${fwA}_vs_${fwB}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Initial render
    renderSideBySide();
    renderGapAnalysis();
});
</script>
@endpush
