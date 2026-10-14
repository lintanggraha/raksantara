<script>
(function () {
    'use strict';

    const config = {
        sectionUrl: @json(route('dashboard.sections', ['section' => '__SECTION__'])),
        cellUrl: @json(route('dashboard.heatmap-cell')),
        query: @json($filterQuery),
    };

    const LEVELS = ['High', 'Moderate to High', 'Moderate', 'Low to Moderate', 'Low'];
    const LEVEL_LABELS = {
        'High': 'Tinggi',
        'Moderate to High': 'Menengah–tinggi',
        'Moderate': 'Menengah',
        'Low to Moderate': 'Rendah–menengah',
        'Low': 'Rendah',
        'Belum dinilai': 'Belum dinilai',
    };
    const TYPE_LABELS = {
        appetite: 'Appetite',
        kri: 'KRI',
        treatment: 'Treatment',
        incident: 'Insiden',
        decision: 'Komite',
    };
    const APPETITE_BADGES = {
        breach: ['bg-red-lt', 'Melewati toleransi'],
        warning: ['bg-orange-lt', 'Di atas target'],
        within: ['bg-green-lt', 'Dalam appetite'],
        qualitative: ['bg-secondary-lt', 'Kualitatif'],
        unconfigured: ['bg-secondary-lt', 'Belum diatur'],
    };
    const charts = {};
    const numberFormat = new Intl.NumberFormat('id-ID');
    const dateFormat = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    const timeFormat = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function num(value) {
        return value === null || value === undefined ? '–' : numberFormat.format(value);
    }

    function rupiah(value) {
        const amount = Number(value || 0);
        const abs = Math.abs(amount);
        if (abs >= 1e12) return 'Rp ' + (amount / 1e12).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' T';
        if (abs >= 1e9) return 'Rp ' + (amount / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' M';
        if (abs >= 1e6) return 'Rp ' + (amount / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' Jt';
        return 'Rp ' + numberFormat.format(Math.round(amount));
    }

    function shortDate(value) {
        if (!value) return '–';
        const date = new Date(value.length === 10 ? value + 'T00:00:00' : value);
        return isNaN(date) ? esc(value) : dateFormat.format(date);
    }

    function levelClass(level) {
        return 'lvl-' + String(level || 'Belum dinilai').replace(/\s+/g, '-');
    }

    function sectionUrl(section, extra) {
        const params = new URLSearchParams(Object.assign({}, config.query, extra || {}));
        return config.sectionUrl.replace('__SECTION__', section) + (params.toString() ? '?' + params : '');
    }

    function emptyState(title, text) {
        return '<div class="grc-state"><strong>' + esc(title) + '</strong>' + (text ? esc(text) : '') + '</div>';
    }

    /**
     * Change versus a previous value. For risk measures an increase is bad.
     */
    function delta(current, previous, label, formatter) {
        if (previous === null || previous === undefined) return '';
        const fmt = formatter || num;
        const diff = Number(current) - Number(previous);
        if (diff === 0) return 'Sama dengan ' + esc(label) + ' (' + fmt(previous) + ')';
        const cls = diff > 0 ? 'grc-delta-up' : 'grc-delta-down';
        const arrow = diff > 0 ? '▲' : '▼';
        const amount = formatter ? fmt(Math.abs(diff)) : num(Math.abs(diff));
        return '<span class="' + cls + '">' + arrow + ' ' + amount + '</span> vs ' + esc(label) + ' (' + fmt(previous) + ')';
    }

    function destroyChart(key) {
        if (charts[key]) {
            charts[key].destroy();
            delete charts[key];
        }
    }

    function load(section, render) {
        const host = document.querySelector('[data-section="' + section + '"]');
        if (!host) return;
        const body = host.querySelector('[data-body]') || host;

        fetch(sectionUrl(section), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then((payload) => render(payload.data, body, host, payload))
            .catch(() => {
                const html = '<div class="grc-state"><strong>Bagian ini gagal dimuat.</strong>'
                    + 'Periksa koneksi lalu coba lagi.<br><button type="button" class="btn btn-sm btn-outline-secondary" data-retry>Coba lagi</button></div>';
                if (host === body && section === 'summary') {
                    body.innerHTML = '<div class="col-12"><div class="card">' + html + '</div></div>';
                } else {
                    body.innerHTML = html;
                }
                body.querySelector('[data-retry]')?.addEventListener('click', () => load(section, render));
            });
    }

    // ------------------------------------------------------------------
    // A: status tiles
    // ------------------------------------------------------------------

    let attentionFilter = 'all';
    let attentionState = null;

    function renderSummary(data, host, _host, payload) {
        const a = data.appetite, k = data.kri, o = data.overdue, l = data.loss, i = data.incidents, q = data.data_quality;
        const period = data.period;

        const tiles = [
            {
                filter: 'appetite',
                label: 'Di luar appetite',
                value: num(a.breach),
                state: a.breach > 0 ? 'critical' : (a.warning > 0 ? 'warning' : ''),
                detail: num(a.warning) + ' di atas target · ' + num(a.evaluated) + ' dari ' + num(q.active_risks) + ' risk punya appetite terukur',
                compare: a.previous_breach === null
                    ? 'Pembanding muncul setelah snapshot akhir bulan pertama'
                    : delta(a.breach, a.previous_breach, 'akhir bulan lalu'),
            },
            {
                filter: 'kri',
                label: 'KRI merah / kuning',
                value: num(k.red) + ' / ' + num(k.amber),
                state: k.red > 0 ? 'critical' : (k.amber > 0 ? 'warning' : ''),
                detail: num(k.total) + ' KRI dipantau' + (k.no_data > 0 ? ' · ' + num(k.no_data) + ' belum ada data' : ''),
                compare: k.stale > 0
                    ? '<span class="grc-delta-up">' + num(k.stale) + ' KRI basi</span>: belum diperbarui sesuai jadwal'
                    : (k.total === k.no_data ? 'Belum ada nilai KRI yang dicatat' : 'Semua sumber data KRI terbaru'),
            },
            {
                filter: 'overdue',
                label: 'Aksi terlambat',
                value: num(o.total),
                state: o.total > 0 ? 'critical' : '',
                detail: num(o.treatments) + ' treatment · ' + num(o.incident_actions) + ' CAPA insiden · ' + num(o.committee_decisions) + ' keputusan komite',
                compare: 'Lewat tenggat dan belum selesai',
            },
            {
                filter: 'incident',
                label: 'Loss event · ' + period.label,
                value: rupiah(l.amount),
                state: l.amount > l.previous_amount ? 'warning' : '',
                detail: num(l.events) + ' kejadian',
                compare: delta(l.amount, l.previous_amount, period.previous_label, rupiah),
            },
            {
                filter: 'incident',
                label: 'Insiden terbuka',
                value: num(i.open),
                state: i.open_severe > 0 ? 'critical' : (i.open > 0 ? 'warning' : ''),
                detail: num(i.open_severe) + ' kritis/tinggi · ' + num(i.reported) + ' terjadi ' + period.label,
                compare: delta(i.reported, i.previous_reported, period.previous_label),
            },
        ];

        host.innerHTML = tiles.map((tile) => (
            '<div class="col-12 col-sm-6 col-lg">'
            + '<button type="button" class="grc-tile" data-tile-filter="' + tile.filter + '" aria-pressed="false" data-state="' + tile.state + '"'
            + ' title="Tampilkan item terkait di Perlu perhatian">'
            + '<span class="grc-tile-label">' + esc(tile.label) + '</span>'
            + '<span class="grc-tile-value">' + esc(tile.value) + '</span>'
            + '<span class="grc-tile-detail">' + esc(tile.detail) + '</span>'
            + '<span class="grc-tile-compare">' + tile.compare + '</span>'
            + '</button></div>'
        )).join('');

        host.querySelectorAll('[data-tile-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.dataset.tileFilter;
                setAttentionFilter(attentionFilter === target ? 'all' : target);
                document.querySelector('[data-section="attention"]')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });

        const asof = document.querySelector('[data-asof]');
        if (asof) {
            const gaps = [];
            if (q.without_residual > 0) gaps.push(num(q.without_residual) + ' risk belum punya analisis residual');
            if (q.without_treatment > 0) gaps.push(num(q.without_treatment) + ' risk tanpa treatment');
            asof.innerHTML = 'Data per ' + esc(timeFormat.format(new Date(payload.generated_at)))
                + (gaps.length ? ' · <span class="grc-warn-text">' + esc(gaps.join(' · ')) + '</span>' : '');
            asof.hidden = false;
        }
    }

    // ------------------------------------------------------------------
    // B: needs attention
    // ------------------------------------------------------------------

    const FILTER_TYPES = {
        all: null,
        appetite: ['appetite'],
        kri: ['kri'],
        overdue: ['treatment', 'decision'],
        incident: ['incident'],
        treatment: ['treatment'],
        decision: ['decision'],
    };

    function setAttentionFilter(filter) {
        attentionFilter = filter;
        document.querySelectorAll('[data-tile-filter]').forEach((tile) => {
            tile.setAttribute('aria-pressed', String(tile.dataset.tileFilter === filter));
        });
        if (attentionState) drawAttention();
    }

    function renderAttention(data, body, host) {
        attentionState = { data, body, host };
        drawAttention();
    }

    function drawAttention() {
        const { data, body, host } = attentionState;
        const chipsHost = host.querySelector('[data-attention-filters]');

        if (!data.items.length) {
            chipsHost.hidden = true;
            body.innerHTML = emptyState('Tidak ada pengecualian.', 'Semua risk berada dalam appetite, tidak ada KRI merah, dan tidak ada aksi yang lewat tenggat.');
            return;
        }

        const chips = [['all', 'Semua', data.items.length]].concat(
            Object.entries(data.counts).map(([type, count]) => [type, TYPE_LABELS[type] || type, count])
        );
        chipsHost.innerHTML = chips.map(([key, label, count]) => (
            '<button type="button" class="grc-chip" data-chip="' + esc(key) + '" aria-pressed="' + (attentionFilter === key) + '">'
            + esc(label) + ' ' + num(count) + '</button>'
        )).join('');
        chipsHost.hidden = false;
        chipsHost.querySelectorAll('[data-chip]').forEach((chip) => chip.addEventListener('click', () => setAttentionFilter(chip.dataset.chip)));

        const types = FILTER_TYPES[attentionFilter];
        const visible = data.items.filter((item) => !types || types.includes(item.type));
        const shown = visible.slice(0, 15);

        if (!shown.length) {
            body.innerHTML = emptyState('Tidak ada item untuk filter ini.', '');
            return;
        }

        body.innerHTML = '<ul class="grc-attn">' + shown.map((item) => (
            '<li>'
            + '<span class="sev sev-' + esc(item.severity) + '" title="' + esc(item.severity) + '"></span>'
            + '<div><a class="title" href="' + esc(item.url) + '">' + esc(item.title) + '</a>'
            + '<div class="meta">' + [item.code, item.owner, item.detail].filter(Boolean).map(esc).join(' · ') + '</div></div>'
            + '<span class="age ' + (item.days > 30 ? 'text-danger' : 'text-muted') + '">'
            + (item.days !== null && item.days !== undefined ? num(item.days) + ' hari' : '') + '</span>'
            + '</li>'
        )).join('') + '</ul>'
            + (visible.length > shown.length ? '<div class="grc-card-sub px-3 py-2 border-top">Menampilkan ' + shown.length + ' dari ' + visible.length + ' item.</div>' : '');
    }

    // ------------------------------------------------------------------
    // C: heatmaps
    // ------------------------------------------------------------------

    function renderHeatmap(data, body) {
        if (!data.inherent.total && !data.residual.total) {
            body.innerHTML = emptyState('Belum ada risk yang dinilai.', 'Heatmap terisi setelah analisis inheren atau residual dicatat.');
            return;
        }

        const grid = (kind, matrix) => {
            let html = '<div><div class="grc-heat-title">' + (kind === 'inherent' ? 'Inheren' : 'Residual') + ' <span class="text-muted fw-normal">· ' + num(matrix.total) + ' risk</span></div><div class="grc-heat">';
            matrix.rows.forEach((row) => {
                html += '<span class="axis">' + row[0].p + '</span>';
                row.forEach((cell) => {
                    const empty = cell.count === 0;
                    html += '<button type="button" class="' + levelClass(cell.level) + (empty ? ' empty' : '') + '"'
                        + (empty ? ' disabled' : '')
                        + ' data-cell data-kind="' + kind + '" data-p="' + cell.p + '" data-i="' + cell.i + '"'
                        + ' aria-label="' + kind + ' kemungkinan ' + cell.p + ' dampak ' + cell.i + ': ' + cell.count + ' risk"'
                        + ' aria-pressed="false">' + (empty ? '' : cell.count) + '</button>';
                });
            });
            html += '<span></span>' + [1, 2, 3, 4, 5].map((i) => '<span class="axis">' + i + '</span>').join('');
            html += '</div><div class="grc-heat-axis-label">Kemungkinan (baris) × Dampak (kolom)</div></div>';
            return html;
        };

        body.innerHTML = '<div class="grc-heat-pair">' + grid('inherent', data.inherent) + grid('residual', data.residual) + '</div>'
            + '<div class="grc-heat-legend">' + LEVELS.map((level) => (
                '<span style="--swatch: var(' + ({
                    'High': '--grc-critical', 'Moderate to High': '--grc-high', 'Moderate': '--grc-medium',
                    'Low to Moderate': '--grc-low-mod', 'Low': '--grc-low',
                })[level] + ')">' + esc(LEVEL_LABELS[level]) + '</span>'
            )).join('') + '</div>'
            + '<div class="grc-cell-detail" data-cell-detail hidden></div>';

        body.querySelectorAll('[data-cell]').forEach((button) => button.addEventListener('click', () => openCell(body, button)));
    }

    function openCell(body, button) {
        const detail = body.querySelector('[data-cell-detail]');
        const wasPressed = button.getAttribute('aria-pressed') === 'true';
        body.querySelectorAll('[data-cell]').forEach((cell) => cell.setAttribute('aria-pressed', 'false'));
        if (wasPressed) {
            detail.hidden = true;
            return;
        }
        button.setAttribute('aria-pressed', 'true');
        detail.hidden = false;
        detail.innerHTML = '<div class="placeholder-glow"><span class="placeholder col-12 mb-2"></span><span class="placeholder col-10"></span></div>';

        const params = new URLSearchParams({
            kind: button.dataset.kind, p: button.dataset.p, i: button.dataset.i,
            ...(config.query.owner ? { owner: config.query.owner } : {}),
            ...(config.query.category ? { category: config.query.category } : {}),
        });
        fetch(config.cellUrl + '?' + params, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then((response) => {
                if (!response.ok) throw new Error();
                return response.json();
            })
            .then(({ data }) => {
                const title = (data.kind === 'inherent' ? 'Inheren' : 'Residual') + ' · kemungkinan ' + data.probability + ', dampak ' + data.impact;
                detail.innerHTML = '<div class="grc-heat-title">' + esc(title) + '</div>'
                    + (data.items.length
                        ? '<ul class="list-unstyled mb-0 small">' + data.items.map((risk) => (
                            '<li class="py-1"><a href="' + esc(risk.url) + '">' + esc(risk.code) + ' · ' + esc(risk.title) + '</a>'
                            + ' <span class="text-muted">' + esc(risk.owner) + '</span></li>'
                        )).join('') + '</ul>'
                        : '<div class="text-muted small">Tidak ada risk di sel ini.</div>');
            })
            .catch(() => { detail.innerHTML = '<div class="text-danger small">Daftar risk gagal dimuat.</div>'; });
    }

    // ------------------------------------------------------------------
    // D: profile per category
    // ------------------------------------------------------------------

    function renderProfile(data, body) {
        if (!data.categories.length) {
            body.innerHTML = emptyState('Belum ada risk aktif.', '');
            return;
        }
        const max = Math.max(...data.categories.map((row) => row.total));
        body.innerHTML = '<div class="grc-bars">' + data.categories.map((row) => {
            const segments = data.levels.filter((level) => row.counts[level] > 0).map((level) => (
                '<span class="' + levelClass(level) + '" style="width:' + (row.counts[level] / max * 100) + '%"'
                + ' title="' + esc(LEVEL_LABELS[level] || level) + ': ' + row.counts[level] + '"></span>'
            )).join('');
            return '<div class="grc-bar-row"><span class="name" title="' + esc(row.category) + '">' + esc(row.category) + '</span>'
                + '<span class="grc-stack" role="img" aria-label="' + esc(row.category + ': ' + data.levels.map((level) => (LEVEL_LABELS[level] || level) + ' ' + row.counts[level]).join(', ')) + '">' + segments + '</span>'
                + '<span class="total">' + num(row.total) + '</span></div>';
        }).join('') + '</div>'
            + '<div class="grc-heat-legend">' + data.levels.map((level) => (
                '<span style="--swatch: ' + ({
                    'High': 'var(--grc-critical)', 'Moderate to High': 'var(--grc-high)', 'Moderate': 'var(--grc-medium)',
                    'Low to Moderate': 'var(--grc-low-mod)', 'Low': 'var(--grc-low)',
                }[level] || '#c5cad3') + '">' + esc(LEVEL_LABELS[level] || level) + '</span>'
            )).join('') + '</div>';
    }

    // ------------------------------------------------------------------
    // E: top risks
    // ------------------------------------------------------------------

    function renderTopRisks(data, body) {
        if (!data.items.length) {
            body.innerHTML = emptyState('Belum ada risk aktif.', '');
            return;
        }
        body.innerHTML = '<div class="table-responsive"><table class="table table-vcenter card-table grc-table"><thead><tr>'
            + '<th>Risk</th><th>Skor</th><th>Appetite</th><th>Treatment</th><th>KRI</th></tr></thead><tbody>'
            + data.items.map((risk) => {
                const badge = APPETITE_BADGES[risk.appetite] || APPETITE_BADGES.unconfigured;
                const t = risk.treatments;
                const treatment = t.total === 0
                    ? '<span class="text-danger small">Belum ada</span>'
                    : '<span class="grc-progress">' + t.completed + '/' + t.total + ' selesai</span>'
                        + (t.overdue ? ' <span class="badge bg-red-lt">' + t.overdue + ' terlambat</span>' : '');
                const kri = (risk.kri.red ? '<span class="badge bg-red-lt">' + risk.kri.red + ' merah</span> ' : '')
                    + (risk.kri.amber ? '<span class="badge bg-yellow-lt">' + risk.kri.amber + ' kuning</span>' : '')
                    || '<span class="text-muted small">–</span>';
                return '<tr>'
                    + '<td><a href="' + esc(risk.url) + '" class="fw-medium">' + esc(risk.code) + '</a>'
                    + '<div class="small text-truncate" style="max-width: 260px" title="' + esc(risk.title) + '">' + esc(risk.title) + '</div>'
                    + '<div class="small text-muted">' + esc(risk.owner) + '</div></td>'
                    + '<td><span class="grc-score">' + (risk.inherent ?? '–') + ' <span class="arrow">→</span> <strong>' + (risk.residual ?? '–') + '</strong></span>'
                    + (risk.level ? '<div class="small text-muted">' + esc(LEVEL_LABELS[risk.level] || risk.level) + '</div>' : '') + '</td>'
                    + '<td><span class="badge ' + badge[0] + '">' + esc(badge[1]) + '</span></td>'
                    + '<td>' + treatment + '</td>'
                    + '<td>' + kri + '</td>'
                    + '</tr>';
            }).join('') + '</tbody></table></div>';
    }

    // ------------------------------------------------------------------
    // F: KRI watchlist
    // ------------------------------------------------------------------

    function thresholdText(kri) {
        // lower_is_better turns red as the value rises; higher_is_better as it falls.
        const op = kri.direction === 'higher_is_better' ? '≤' : '≥';
        const parts = [];
        if (kri.warn !== null) parts.push('kuning ' + op + ' ' + num(kri.warn));
        if (kri.critical !== null) parts.push('merah ' + op + ' ' + num(kri.critical));
        return parts.join(' · ');
    }

    function renderKris(data, body) {
        Object.keys(charts).filter((key) => key.startsWith('kri-')).forEach(destroyChart);
        const noDataNote = data.no_data_total > 0
            ? '<div class="grc-card-sub px-3 py-2 border-top">' + num(data.no_data_total) + ' KRI belum punya nilai pertama. Isi nilainya di halaman KRI agar ikut dipantau.</div>'
            : '';
        if (!data.items.length) {
            body.innerHTML = (data.no_data_total > 0
                ? emptyState('Belum ada KRI yang merah, kuning, atau basi.', '')
                : emptyState('Semua KRI hijau dan datanya terbaru.', '')) + noDataNote;
            return;
        }
        body.innerHTML = '<div class="table-responsive"><table class="table table-vcenter card-table grc-table"><thead><tr>'
            + '<th>KRI</th><th>Nilai</th><th>Tren</th><th>Status</th></tr></thead><tbody>'
            + data.items.map((kri, index) => {
                const status = String(kri.status).toLowerCase();
                const badge = status === 'red' ? 'bg-red-lt">Merah' : (status === 'amber' ? 'bg-yellow-lt">Kuning' : 'bg-green-lt">Hijau');
                return '<tr>'
                    + '<td><a href="' + esc(kri.url) + '" class="fw-medium">' + esc(kri.metric) + '</a>'
                    + '<div class="small text-muted">' + esc(kri.code) + ' · ' + esc(kri.owner) + '</div></td>'
                    + '<td class="num"><strong>' + num(kri.value) + '</strong><div class="small text-muted">' + esc(thresholdText(kri)) + '</div></td>'
                    + '<td>' + (kri.history.length > 1 ? '<canvas class="grc-spark" data-spark="' + index + '" role="img" aria-label="Tren ' + esc(kri.metric) + '"></canvas>' : '<span class="small text-muted">Belum cukup data</span>') + '</td>'
                    + '<td><span class="badge ' + badge + '</span>'
                    + (kri.stale ? ' <span class="badge bg-secondary-lt" title="Diperbarui ' + esc(shortDate(kri.updated_at)) + '">Basi</span>' : '') + '</td>'
                    + '</tr>';
            }).join('') + '</tbody></table></div>' + noDataNote;

        if (typeof Chart === 'undefined') return;
        body.querySelectorAll('[data-spark]').forEach((canvas) => {
            const kri = data.items[Number(canvas.dataset.spark)];
            const status = String(kri.status).toLowerCase();
            const color = status === 'red' ? '#d63939' : (status === 'amber' ? '#f59f00' : '#667382');
            charts['kri-' + canvas.dataset.spark] = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: kri.history.map((point) => point.at),
                    datasets: [{ data: kri.history.map((point) => point.value), borderColor: color, borderWidth: 1.5, pointRadius: 0, tension: 0.25, fill: false }],
                },
                options: {
                    responsive: false, animation: false, events: [],
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: { x: { display: false }, y: { display: false } },
                },
            });
        });
    }

    // ------------------------------------------------------------------
    // G: losses
    // ------------------------------------------------------------------

    function renderLosses(data, body) {
        destroyChart('losses');
        if (!data.total_events) {
            body.innerHTML = emptyState('Tidak ada loss event dalam 12 bulan.', 'Insiden dengan nilai kerugian akan tampil di sini.');
            return;
        }
        body.innerHTML = '<div class="d-flex gap-4 mb-2 small"><div><div class="text-muted">Total 12 bulan</div><strong>' + esc(rupiah(data.total_amount)) + '</strong></div>'
            + '<div><div class="text-muted">Kejadian</div><strong>' + num(data.total_events) + '</strong></div></div>'
            + '<div class="grc-chart"><canvas data-loss-chart role="img" aria-label="Loss event per bulan"></canvas></div>'
            + (data.largest.length ? '<div class="grc-heat-title mt-3">Loss terbesar</div><ul class="list-unstyled grc-largest mb-0">' + data.largest.map((event) => (
                '<li><a href="' + esc(event.url) + '" class="text-truncate">' + esc(event.code) + ' · ' + esc(event.title) + '</a>'
                + '<span class="num text-nowrap">' + esc(rupiah(event.amount)) + '</span></li>'
            )).join('') + '</ul>' : '');

        if (typeof Chart === 'undefined') return;
        charts.losses = new Chart(body.querySelector('[data-loss-chart]'), {
            type: 'bar',
            data: {
                labels: data.series.map((point) => point.label),
                datasets: [{ label: 'Loss', data: data.series.map((point) => point.amount), backgroundColor: '#1e3a8a', borderRadius: 3, maxBarThickness: 28 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => rupiah(ctx.parsed.y) + ' · ' + data.series[ctx.dataIndex].events + ' kejadian' } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#667382', font: { size: 11 } } },
                    y: { beginAtZero: true, grid: { color: '#eef0f3' }, ticks: { color: '#667382', font: { size: 11 }, callback: (value) => rupiah(value) } },
                },
            },
        });
    }

    // ------------------------------------------------------------------
    // H: follow-up progress
    // ------------------------------------------------------------------

    function renderActions(data, body) {
        const d = data.decisions;
        if (!data.units.length && !(d.open + d.overdue + d.closed)) {
            body.innerHTML = emptyState('Belum ada treatment atau keputusan komite.', '');
            return;
        }
        const buckets = [['completed', 'Selesai'], ['on_track', 'Berjalan'], ['blocked', 'Terhambat'], ['overdue', 'Terlambat']];
        const max = Math.max(1, ...data.units.map((unit) => unit.total));

        let html = '';
        if (data.units.length) {
            html += '<div class="grc-bars">' + data.units.map((unit) => (
                '<div class="grc-bar-row"><span class="name" title="' + esc(unit.owner) + '">' + esc(unit.owner) + '</span>'
                + '<span class="grc-stack" role="img" aria-label="' + esc(unit.owner + ': ' + buckets.map(([key, label]) => label + ' ' + unit.counts[key]).join(', ')) + '">'
                + buckets.filter(([key]) => unit.counts[key] > 0).map(([key, label]) => (
                    '<span class="seg-' + key + '" style="width:' + (unit.counts[key] / max * 100) + '%" title="' + label + ': ' + unit.counts[key] + '"></span>'
                )).join('') + '</span><span class="total">' + num(unit.total) + '</span></div>'
            )).join('') + '</div>'
                + '<div class="grc-heat-legend">' + buckets.map(([key, label]) => (
                    '<span style="--swatch: var(' + ({ completed: '--grc-low', on_track: '--grc-muted', blocked: '--grc-medium', overdue: '--grc-critical' })[key] + ')">' + label + ' ' + num(data.totals[key]) + '</span>'
                )).join('') + '</div>'
                + (data.units_total > data.units.length ? '<div class="grc-card-sub mt-1">' + (data.units_total - data.units.length) + ' unit lain tidak ditampilkan.</div>' : '');
        }

        html += '<div class="grc-heat-title mt-3">Keputusan komite</div>'
            + '<div class="small mb-2">' + num(d.open) + ' terbuka · <span class="' + (d.overdue ? 'text-danger fw-medium' : '') + '">' + num(d.overdue) + ' terlambat</span> · ' + num(d.closed) + ' selesai</div>'
            + (d.next.length ? '<ul class="list-unstyled small mb-0">' + d.next.map((decision) => (
                '<li class="py-1 d-flex justify-content-between gap-2"><a href="' + esc(decision.url) + '" class="text-truncate">' + esc(decision.title) + '</a>'
                + '<span class="text-nowrap ' + (decision.overdue ? 'text-danger' : 'text-muted') + '">' + (decision.due_at ? shortDate(decision.due_at) : 'Tanpa tenggat') + '</span></li>'
            )).join('') + '</ul>' : '');

        body.innerHTML = html;
    }

    // ------------------------------------------------------------------
    // I: assurance due
    // ------------------------------------------------------------------

    function renderAssurance(data, body) {
        if (!data.items.length) {
            body.innerHTML = emptyState('Tidak ada assurance yang jatuh tempo.', 'Dalam ' + data.horizon_days + ' hari ke depan semua jadwal review dan uji aman.');
            return;
        }
        const today = new Date(); today.setHours(0, 0, 0, 0);
        body.innerHTML = '<div class="grc-card-sub px-3 pt-3">' + num(data.counts.overdue) + ' terlambat · ' + num(data.counts.upcoming) + ' dalam ' + data.horizon_days + ' hari</div>'
            + '<ul class="grc-attn">' + data.items.map((item) => {
                let when = 'Belum pernah';
                if (item.due) {
                    const days = Math.round((new Date(item.due + 'T00:00:00') - today) / 86400000);
                    when = days < 0 ? Math.abs(days) + ' hari lalu' : (days === 0 ? 'Hari ini' : days + ' hari lagi');
                }
                return '<li><span class="sev ' + (item.overdue ? 'sev-critical' : 'sev-medium') + '"></span>'
                    + '<div><a class="title" href="' + esc(item.url) + '">' + esc(item.label) + '</a><div class="meta">' + esc(item.subject) + '</div></div>'
                    + '<span class="age ' + (item.overdue ? 'text-danger' : 'text-muted') + '">' + esc(when) + '</span></li>';
            }).join('') + '</ul>';
    }

    // ------------------------------------------------------------------
    // J: posture trend
    // ------------------------------------------------------------------

    function renderTrend(data, body) {
        destroyChart('trend');
        if (!data.series.length) {
            body.innerHTML = emptyState('Tren belum tersedia.', 'Snapshot skor diambil otomatis setiap akhir bulan. Grafik terisi mulai snapshot pertama; riwayat sebelum itu tidak direkonstruksi.');
            return;
        }
        const last = data.series[data.series.length - 1];
        body.innerHTML = '<div class="d-flex gap-4 mb-2 small">'
            + '<div><div class="text-muted">Melewati toleransi</div><strong>' + num(last.breaches) + '</strong></div>'
            + '<div><div class="text-muted">Risk tinggi</div><strong>' + num(last.high_risks) + '</strong></div>'
            + '<div><div class="text-muted">Rata-rata residual</div><strong>' + (last.avg_residual ?? '–') + '</strong></div></div>'
            + '<div class="grc-chart"><canvas data-trend-chart role="img" aria-label="Tren postur risiko per akhir bulan"></canvas></div>'
            + '<div class="grc-card-sub mt-1">Snapshot pertama: ' + esc(shortDate(data.first_period)) + '</div>';

        if (typeof Chart === 'undefined') return;
        charts.trend = new Chart(body.querySelector('[data-trend-chart]'), {
            type: 'line',
            data: {
                labels: data.series.map((point) => point.label),
                datasets: [
                    { label: 'Melewati toleransi', data: data.series.map((point) => point.breaches), borderColor: '#d63939', backgroundColor: '#d63939', tension: 0.2, pointRadius: 3 },
                    { label: 'Risk tinggi', data: data.series.map((point) => point.high_risks), borderColor: '#f76707', backgroundColor: '#f76707', tension: 0.2, pointRadius: 3 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#667382', font: { size: 11 } } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#667382', font: { size: 11 } }, grid: { color: '#eef0f3' } },
                },
            },
        });
    }

    // ------------------------------------------------------------------

    load('summary', renderSummary);
    load('attention', renderAttention);
    load('heatmap', renderHeatmap);
    load('profile', renderProfile);
    load('top-risks', renderTopRisks);
    load('kris', renderKris);
    load('losses', renderLosses);
    load('actions', renderActions);
    load('assurance', renderAssurance);
    load('trend', renderTrend);
})();
</script>
