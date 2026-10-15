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
    const LEVEL_SWATCH = {
        'High': 'var(--grc-critical)', 'Moderate to High': 'var(--grc-high)', 'Moderate': 'var(--grc-medium)',
        'Low to Moderate': 'var(--grc-low-mod)', 'Low': 'var(--grc-low)', 'Belum dinilai': '#c5cad3',
    };
    const TYPE_LABELS = {
        appetite: 'Appetite',
        kri: 'KRI',
        treatment: 'Treatment',
        incident: 'Insiden',
        decision: 'Komite',
    };
    const AGING = [['d30', '≤30 hari'], ['d60', '31–60 hari'], ['d90', '61–90 hari'], ['d90_plus', '>90 hari']];
    const charts = {};
    const numberFormat = new Intl.NumberFormat('id-ID');
    const dateFormat = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    const dayMonth = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short' });
    const timeFormat = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    /** data-tip attribute. `html` is trusted markup; escape user text inside it first. */
    function tipAttr(html) {
        return ' data-tip="' + esc(html) + '"';
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

    function legend(entries, extraClass) {
        return '<div class="grc-heat-legend ' + (extraClass || '') + '">' + entries.map(([swatch, label, cls]) => (
            '<span class="' + (cls || '') + '" style="--swatch: ' + swatch + '">' + esc(label) + '</span>'
        )).join('') + '</div>';
    }

    /** Short change versus a previous value. For risk measures an increase is bad. */
    function delta(current, previous, label, asPercent) {
        if (previous === null || previous === undefined) return '';
        const diff = Number(current) - Number(previous);
        if (diff === 0) return '= ' + esc(label);
        const cls = diff > 0 ? 'grc-delta-up' : 'grc-delta-down';
        const arrow = diff > 0 ? '▲' : '▼';
        const amount = asPercent
            ? (previous ? Math.round(Math.abs(diff) / previous * 100) + '%' : 'baru')
            : num(Math.abs(diff));
        return '<span class="' + cls + '">' + arrow + ' ' + amount + '</span> vs ' + esc(label);
    }

    /** 12-point trend line with a wash and an emphasised endpoint. */
    function spark(values, width, height, color) {
        if (!values || values.length < 2) return '';
        const nums = values.map(Number);
        const min = Math.min(...nums), max = Math.max(...nums);
        const pad = 5, span = max - min || 1;
        const pts = nums.map((v, i) => [
            pad + i * (width - pad * 2) / (nums.length - 1),
            pad + (1 - (v - min) / span) * (height - pad * 2),
        ]);
        const line = pts.map((p) => p[0].toFixed(1) + ',' + p[1].toFixed(1)).join(' ');
        const last = pts[pts.length - 1];
        const area = 'M' + pts[0][0].toFixed(1) + ',' + (height - 1) + ' L' + line.split(' ').join(' L') + ' L' + last[0].toFixed(1) + ',' + (height - 1) + ' Z';
        return '<svg class="grc-spark" width="' + width + '" height="' + height + '" viewBox="0 0 ' + width + ' ' + height + '" aria-hidden="true">'
            + '<path d="' + area + '" style="fill:' + color + ';fill-opacity:.1"></path>'
            + '<polyline points="' + line + '" style="fill:none;stroke:' + color + ';stroke-width:2;stroke-linejoin:round;stroke-linecap:round"></polyline>'
            + '<circle cx="' + last[0].toFixed(1) + '" cy="' + last[1].toFixed(1) + '" r="4" style="fill:' + color + ';stroke:#fff;stroke-width:2"></circle></svg>';
    }

    function clampPct(value, lo, hi) {
        return Math.max(0, Math.min(100, (value - lo) / ((hi - lo) || 1) * 100));
    }

    function destroyChart(key) {
        if (charts[key]) {
            charts[key].destroy();
            delete charts[key];
        }
    }

    function chartTooltip() {
        return { backgroundColor: '#182433', titleColor: '#fff', bodyColor: '#fff', padding: 8, cornerRadius: 6, boxPadding: 4, usePointStyle: true };
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
                const targets = host === body ? [body] : Array.from(host.querySelectorAll('[data-body]'));
                targets.forEach((target) => {
                    target.innerHTML = host === body && section === 'summary'
                        ? '<div class="card" style="grid-column: 1 / -1;">' + html + '</div>'
                        : html;
                    target.querySelector('[data-retry]')?.addEventListener('click', () => load(section, render));
                });
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
        const history = data.history || {};
        const prevLabel = period.key === 'quarter' ? 'kuartal lalu' : 'bulan lalu';

        const kriMix = k.total
            ? '<span class="grc-tile-mix" aria-hidden="true">'
                + [[k.red, 'var(--grc-critical)'], [k.amber, 'var(--grc-medium)'], [k.green, 'var(--grc-low)'], [k.no_data, '#c5cad3']]
                    .filter(([n]) => n > 0).map(([n, color]) => '<span style="flex:' + n + ';background:' + color + '"></span>').join('')
                + '</span>'
            : '';

        const tiles = [
            {
                filter: 'appetite',
                label: 'Di luar appetite',
                value: num(a.breach),
                state: a.breach > 0 ? 'critical' : (a.warning > 0 ? 'warning' : ''),
                visual: spark(history.breaches, 92, 40, a.breach > 0 ? 'var(--grc-critical)' : 'var(--grc-muted)'),
                compare: a.previous_breach === null ? 'Belum ada pembanding' : delta(a.breach, a.previous_breach, 'akhir bulan lalu'),
                tip: num(a.breach) + ' melewati toleransi · ' + num(a.warning) + ' di atas target<br>'
                    + num(a.evaluated) + ' dari ' + num(q.active_risks) + ' risk punya appetite terukur'
                    + (a.previous_breach === null ? '<br>Pembanding muncul setelah snapshot akhir bulan pertama.' : ''),
            },
            {
                filter: 'kri',
                label: 'KRI merah / kuning',
                value: num(k.red) + ' <small>/ ' + num(k.amber) + '</small>',
                state: k.red > 0 ? 'critical' : (k.amber > 0 ? 'warning' : ''),
                visual: kriMix,
                compare: k.stale > 0 ? '<span class="grc-delta-up">' + num(k.stale) + ' basi</span> · ' + num(k.total) + ' KRI' : 'dari ' + num(k.total) + ' KRI',
                tip: num(k.red) + ' merah · ' + num(k.amber) + ' kuning · ' + num(k.green) + ' hijau'
                    + (k.no_data > 0 ? ' · ' + num(k.no_data) + ' belum ada data' : '')
                    + (k.stale > 0 ? '<br>' + num(k.stale) + ' KRI belum diperbarui sesuai jadwal' : ''),
            },
            {
                filter: 'overdue',
                label: 'Aksi terlambat',
                value: num(o.total),
                state: o.total > 0 ? 'critical' : '',
                visual: '',
                compare: 'lewat tenggat',
                tip: num(o.treatments) + ' treatment · ' + num(o.incident_actions) + ' CAPA insiden · ' + num(o.committee_decisions) + ' keputusan komite',
            },
            {
                filter: 'incident',
                label: 'Loss event · ' + period.label,
                value: rupiah(l.amount),
                state: l.amount > l.previous_amount ? 'warning' : '',
                visual: spark(history.loss, 92, 40, 'var(--grc-accent)'),
                compare: delta(l.amount, l.previous_amount, prevLabel, true),
                tip: num(l.events) + ' kejadian ' + esc(period.label) + '<br>' + esc(period.previous_label) + ': ' + rupiah(l.previous_amount) + ' · ' + num(l.previous_events) + ' kejadian',
            },
            {
                filter: 'incident',
                label: 'Insiden terbuka',
                value: num(i.open),
                state: i.open_severe > 0 ? 'critical' : (i.open > 0 ? 'warning' : ''),
                visual: spark(history.incidents, 92, 40, 'var(--grc-muted)'),
                compare: num(i.open_severe) + ' kritis/tinggi',
                tip: num(i.reported) + ' terjadi ' + esc(period.label) + ' · ' + num(i.previous_reported) + ' ' + esc(period.previous_label)
                    + '<br>Garis: insiden per bulan, 12 bulan',
            },
        ];

        host.innerHTML = tiles.map((tile) => (
            '<button type="button" class="grc-tile" data-tile-filter="' + tile.filter + '" aria-pressed="false" data-state="' + tile.state + '"'
            + tipAttr(tile.tip + '<br><em>Klik untuk melihat daftar terkait</em>') + '>'
            + '<span class="grc-tile-label">' + esc(tile.label) + '</span>'
            + '<span class="grc-tile-value">' + tile.value + '</span>'
            + (tile.visual ? '<span class="grc-tile-visual">' + tile.visual + '</span>' : '')
            + '<span class="grc-tile-compare">' + tile.compare + '</span>'
            + '</button>'
        )).join('');

        host.querySelectorAll('[data-tile-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.dataset.tileFilter;
                setAttentionFilter(attentionFilter === target ? 'all' : target, true);
            });
        });

        const asof = document.querySelector('[data-asof]');
        if (asof) {
            const gaps = [];
            if (q.without_residual > 0) gaps.push(['risk belum punya analisis residual, jadi tidak masuk heatmap residual dan perhitungan appetite.', num(q.without_residual) + ' tanpa residual']);
            if (q.without_treatment > 0) gaps.push(['risk aktif tanpa satu pun rencana treatment.', num(q.without_treatment) + ' tanpa treatment']);
            asof.innerHTML = 'Data per ' + esc(timeFormat.format(new Date(payload.generated_at)))
                + gaps.map(([tip, label]) => ' <span class="grc-pill is-warning"' + tipAttr(esc(label.split(' ')[0] + ' ' + tip)) + '>' + esc(label) + '</span>').join('');
            asof.hidden = false;
        }
    }

    // ------------------------------------------------------------------
    // A2: portfolio at a glance
    // ------------------------------------------------------------------

    function portfolioCard(body, data, opts) {
        if (!data.total) {
            body.innerHTML = emptyState(opts.empty, '');
            return;
        }
        const max = Math.max(1, ...data.rows.map((row) => row.total));
        const [mainKey, restKey] = opts.keys;
        const swatch = { [mainKey]: opts.mainColor, [restKey]: 'var(--grc-faint)' };

        body.innerHTML = '<div class="grc-portfolio-head">'
            + '<span class="grc-big">' + num(data.total) + '</span>'
            + '<span class="grc-portfolio-sub">' + esc(opts.unit) + ' dari ' + num(data.groups_total) + ' ' + esc(opts.groupUnit) + '<br>'
            + '<b>' + num(data.totals[mainKey]) + '</b> ' + esc(opts.labels[mainKey].toLowerCase()) + ' · ' + num(data.totals[restKey]) + ' ' + esc(opts.labels[restKey].toLowerCase()) + '</span>'
            + '</div>'
            + '<div class="grc-bars">' + data.rows.map((row) => (
                '<div class="grc-bar-row' + (row.other ? ' is-other' : '') + '"><span class="name" title="' + esc(row.name) + '">' + esc(row.name) + '</span>'
                + '<span><span class="grc-stack" style="width:' + (row.total / max * 100) + '%" role="img" aria-label="' + esc(row.name + ': ' + opts.keys.map((key) => opts.labels[key] + ' ' + row.counts[key]).join(', ')) + '">'
                + opts.keys.filter((key) => row.counts[key] > 0).map((key) => (
                    '<span style="flex:' + row.counts[key] + ';background:' + swatch[key] + '"' + tipAttr(esc(row.name) + ' · ' + esc(opts.labels[key]) + ': ' + num(row.counts[key])) + '></span>'
                )).join('')
                + '</span></span><span class="total">' + num(row.total) + '</span></div>'
            )).join('') + '</div>'
            + legend(opts.keys.map((key) => [swatch[key], opts.labels[key]]));
    }

    function renderPortfolio(data, _body, host) {
        portfolioCard(host.querySelector('[data-portfolio="risks"] [data-body]'), data.risks, {
            keys: ['active', 'closed'],
            labels: { active: 'Aktif', closed: 'Ditutup' },
            mainColor: 'var(--grc-accent)',
            unit: 'risk',
            groupUnit: 'divisi',
            empty: 'Belum ada risk di register.',
        });
        portfolioCard(host.querySelector('[data-portfolio="incidents"] [data-body]'), data.incidents, {
            keys: ['open', 'closed'],
            labels: { open: 'Terbuka', closed: 'Selesai' },
            mainColor: 'var(--grc-high)',
            unit: 'insiden',
            groupUnit: 'client',
            empty: 'Belum ada insiden tercatat.',
        });
    }

    // ------------------------------------------------------------------
    // B: follow-up drawer
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

    function setAttentionFilter(filter, reveal) {
        attentionFilter = filter;
        document.querySelectorAll('[data-tile-filter]').forEach((tile) => {
            tile.setAttribute('aria-pressed', String(tile.dataset.tileFilter === filter));
        });
        if (!attentionState) return;
        if (reveal) attentionState.open = true;
        drawAttention();
        if (reveal) {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            attentionState.host.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        }
    }

    function renderAttention(data, body, host) {
        attentionState = { data, body, host, open: attentionState?.open || attentionFilter !== 'all' };
        host.querySelector('[data-attention-toggle]').addEventListener('click', () => {
            attentionState.open = !attentionState.open;
            drawAttention();
        });
        drawAttention();
    }

    function drawAttention() {
        const { data, body, host, open } = attentionState;
        const chipsHost = host.querySelector('[data-attention-filters]');
        const toggle = host.querySelector('[data-attention-toggle]');

        if (!data.items.length) {
            chipsHost.hidden = true;
            toggle.hidden = true;
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
        chipsHost.querySelectorAll('[data-chip]').forEach((chip) => chip.addEventListener('click', () => setAttentionFilter(chip.dataset.chip, true)));

        toggle.hidden = false;
        toggle.setAttribute('aria-expanded', String(open));
        toggle.textContent = open ? 'Sembunyikan' : 'Tampilkan daftar';

        if (!open) {
            body.innerHTML = '';
            return;
        }

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
    // C: heatmap (one grid, residual/inherent toggle)
    // ------------------------------------------------------------------

    function renderHeatmap(data, body, host) {
        if (!data.inherent.total && !data.residual.total) {
            body.innerHTML = emptyState('Belum ada risk yang dinilai.', 'Heatmap terisi setelah analisis inheren atau residual dicatat.');
            return;
        }

        const toggle = host.querySelector('[data-heat-toggle]');
        let kind = data.residual.total ? 'residual' : 'inherent';

        const draw = () => {
            const matrix = data[kind];
            let grid = '';
            matrix.rows.forEach((row) => {
                grid += '<span class="axis">' + row[0].p + '</span>';
                row.forEach((cell) => {
                    const empty = cell.count === 0;
                    grid += '<button type="button" class="' + levelClass(cell.level) + (empty ? ' empty' : '') + '"'
                        + (empty ? ' disabled' : tipAttr(num(cell.count) + ' risk · ' + esc(LEVEL_LABELS[cell.level] || cell.level) + '<br>Kemungkinan ' + cell.p + ' × dampak ' + cell.i))
                        + ' data-cell data-p="' + cell.p + '" data-i="' + cell.i + '"'
                        + ' aria-label="' + kind + ' kemungkinan ' + cell.p + ' dampak ' + cell.i + ': ' + cell.count + ' risk"'
                        + ' aria-pressed="false">' + (empty ? '' : cell.count) + '</button>';
                });
            });
            grid += '<span></span>' + [1, 2, 3, 4, 5].map((i) => '<span class="axis">' + i + '</span>').join('');

            body.innerHTML = '<div class="grc-heat-wrap"><div class="grc-heat-ylab">Kemungkinan</div>'
                + '<div><div class="grc-heat">' + grid + '</div><div class="grc-heat-axis-label">Dampak · ' + num(matrix.total) + ' risk dinilai</div></div></div>'
                + legend(LEVELS.map((level) => [LEVEL_SWATCH[level], LEVEL_LABELS[level]]), 'center')
                + '<div class="grc-cell-detail" data-cell-detail hidden></div>';

            body.querySelectorAll('[data-cell]').forEach((button) => button.addEventListener('click', () => openCell(body, button, kind)));
        };

        toggle.hidden = false;
        toggle.querySelectorAll('[data-heat-kind]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.heatKind === kind));
            button.onclick = () => {
                kind = button.dataset.heatKind;
                toggle.querySelectorAll('[data-heat-kind]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
                draw();
            };
        });
        draw();
    }

    function openCell(body, button, kind) {
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
            kind, p: button.dataset.p, i: button.dataset.i,
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
                '<span class="' + levelClass(level) + '" style="flex:' + row.counts[level] + '"'
                + tipAttr(esc(row.category) + ' · ' + esc(LEVEL_LABELS[level] || level) + ': ' + num(row.counts[level]) + ' risk') + '></span>'
            )).join('');
            return '<div class="grc-bar-row"><span class="name" title="' + esc(row.category) + '">' + esc(row.category) + '</span>'
                + '<span><span class="grc-stack" style="width:' + (row.total / max * 100) + '%" role="img" aria-label="' + esc(row.category + ': ' + data.levels.map((level) => (LEVEL_LABELS[level] || level) + ' ' + row.counts[level]).join(', ')) + '">' + segments + '</span></span>'
                + '<span class="total">' + num(row.total) + '</span></div>';
        }).join('') + '</div>'
            + legend(data.levels.map((level) => [LEVEL_SWATCH[level] || '#c5cad3', LEVEL_LABELS[level] || level]));
    }

    // ------------------------------------------------------------------
    // E: top risks (residual bar over a faded inherent bar, appetite line)
    // ------------------------------------------------------------------

    const SCORE_MAX = 25;
    const APPETITE_PILLS = {
        breach: ['is-critical', 'Lewat toleransi'],
        warning: ['is-warning', 'Di atas appetite'],
        within: ['is-ok', 'Dalam appetite'],
        qualitative: ['is-muted', 'Kualitatif'],
        unconfigured: ['is-muted', 'Belum diatur'],
    };

    function renderTopRisks(data, body) {
        if (!data.items.length) {
            body.innerHTML = emptyState('Belum ada risk aktif.', '');
            return;
        }
        const pct = (value) => clampPct(value, 0, SCORE_MAX);

        body.innerHTML = '<div class="grc-rank">' + data.items.map((risk) => {
            const inherent = risk.inherent, residual = risk.residual;
            const appetite = risk.appetite_limit, tolerance = risk.tolerance_limit;
            const hasAppetite = appetite !== null && appetite !== undefined;
            const pill = APPETITE_PILLS[risk.appetite] || APPETITE_PILLS.unconfigured;

            const tip = esc(risk.code) + ' · ' + esc(risk.title) + '<br>' + esc(risk.owner || '')
                + '<br>Skor inheren ' + num(inherent) + ' → residual ' + num(residual)
                + (hasAppetite
                    ? '<br>Batas appetite ' + num(appetite) + (tolerance !== null && tolerance !== appetite ? ' · toleransi ' + num(tolerance) : '')
                    : '<br>Appetite tidak berbasis skor')
                + '<br>Treatment ' + risk.treatments.completed + '/' + risk.treatments.total + ' selesai'
                + (risk.treatments.overdue ? ' · ' + risk.treatments.overdue + ' terlambat' : '')
                + (risk.kri.red || risk.kri.amber ? '<br>KRI: ' + risk.kri.red + ' merah · ' + risk.kri.amber + ' kuning' : '');

            const bars = (inherent !== null ? '<span class="grc-rank-inherent" style="width:' + pct(inherent) + '%"></span>' : '')
                + (residual !== null ? '<span class="grc-rank-residual ' + levelClass(risk.level) + '" style="width:' + pct(residual) + '%"></span>' : '')
                + (hasAppetite ? '<span class="grc-rank-appetite" style="left:' + pct(appetite) + '%"></span>' : '');

            return '<div class="grc-rank-row"' + tipAttr(tip) + '>'
                + '<div class="grc-rank-head">'
                + '<a href="' + esc(risk.url) + '" class="grc-rank-title" title="' + esc(risk.title) + '"><span class="code">' + esc(risk.code) + '</span> ' + esc(risk.title) + '</a>'
                + '<span class="grc-pill ' + pill[0] + '">' + pill[1] + '</span>'
                + '</div>'
                + '<div class="grc-rank-body">'
                + '<div class="grc-rank-track" role="img" aria-label="' + esc(risk.code + ': skor residual ' + (residual ?? '–') + ' dari inheren ' + (inherent ?? '–') + (hasAppetite ? ', batas appetite ' + appetite : '')) + '">' + bars + '</div>'
                + '<span class="grc-rank-score"><b>' + num(residual) + '</b>' + (inherent !== null ? ' <span>dari ' + num(inherent) + '</span>' : '') + '</span>'
                + '</div>'
                + '</div>';
        }).join('') + '</div>'
            + legend([
                ['var(--grc-medium)', 'Skor residual (warna = level)'],
                ['#dfe3e9', 'Skor inheren'],
                ['', 'Batas appetite', 'tick'],
            ]);
    }

    // ------------------------------------------------------------------
    // F: KRI watchlist (bullet graphs)
    // ------------------------------------------------------------------

    function bullet(kri) {
        const higherIsBetter = kri.direction === 'higher_is_better';
        const values = [kri.value, kri.warn, kri.critical].concat(kri.history.map((point) => point.value)).filter((v) => v !== null && v !== undefined);
        const lo = Math.min(0, ...values);
        const hi = Math.max(...values) * 1.2 || 1;
        const pct = (v) => clampPct(v, lo, hi);
        const band = (cls, from, to) => to > from ? '<span class="band ' + cls + '" style="left:' + from + '%;width:' + (to - from) + '%"></span>' : '';

        let bands;
        if (higherIsBetter) {
            const crit = kri.critical !== null ? pct(kri.critical) : 0;
            const warn = kri.warn !== null ? pct(kri.warn) : crit;
            bands = band('band-crit', 0, crit) + band('band-warn', crit, warn) + band('band-ok', warn, 100);
        } else {
            const warn = kri.warn !== null ? pct(kri.warn) : (kri.critical !== null ? pct(kri.critical) : 100);
            const crit = kri.critical !== null ? pct(kri.critical) : 100;
            bands = band('band-ok', 0, warn) + band('band-warn', warn, crit) + band('band-crit', crit, 100);
        }

        return '<div class="grc-bullet" role="img" aria-label="' + esc(kri.metric + ': ' + num(kri.value)) + '">'
            + bands + '<span class="val" style="width:' + pct(kri.value) + '%"></span></div>';
    }

    function thresholdText(kri) {
        // lower_is_better turns red as the value rises; higher_is_better as it falls.
        const op = kri.direction === 'higher_is_better' ? '≤' : '≥';
        const parts = [];
        if (kri.warn !== null) parts.push('kuning ' + op + ' ' + num(kri.warn));
        if (kri.critical !== null) parts.push('merah ' + op + ' ' + num(kri.critical));
        return parts.join(' · ');
    }

    function renderKris(data, body) {
        const noDataNote = data.no_data_total > 0
            ? '<div class="grc-card-sub pt-2">' + num(data.no_data_total) + ' KRI belum punya nilai pertama. Isi nilainya di halaman KRI agar ikut dipantau.</div>'
            : '';
        if (!data.items.length) {
            body.innerHTML = (data.no_data_total > 0
                ? emptyState('Belum ada KRI yang merah, kuning, atau basi.', '')
                : emptyState('Semua KRI hijau dan datanya terbaru.', '')) + noDataNote;
            return;
        }

        body.innerHTML = '<div class="grc-kri-head"><span>KRI</span><span>Posisi terhadap threshold</span><span>Nilai</span><span>12 data terakhir</span><span>Status</span></div>'
            + data.items.map((kri) => {
                const status = String(kri.status).toLowerCase();
                const color = status === 'red' ? 'var(--grc-critical)' : (status === 'amber' ? 'var(--grc-medium)' : 'var(--grc-muted)');
                const pill = status === 'red' ? '<span class="grc-pill is-critical">Merah</span>'
                    : (status === 'amber' ? '<span class="grc-pill is-warning">Kuning</span>' : '<span class="grc-pill is-ok">Hijau</span>');
                const tip = esc(kri.metric) + ': <b>' + num(kri.value) + '</b>'
                    + (thresholdText(kri) ? '<br>' + esc(thresholdText(kri)) : '')
                    + (kri.direction === 'higher_is_better' ? '<br>Makin tinggi makin baik' : '')
                    + '<br>Diperbarui ' + esc(shortDate(kri.updated_at));
                return '<div class="grc-kri-row"' + tipAttr(tip) + '>'
                    + '<div class="grc-kri-name"><a href="' + esc(kri.url) + '">' + esc(kri.metric) + '</a><span class="meta">' + esc(kri.code) + ' · ' + esc(kri.owner) + '</span></div>'
                    + bullet(kri)
                    + '<div class="grc-kri-value">' + num(kri.value) + '</div>'
                    + '<div class="spark-cell">' + (kri.history.length > 1 ? spark(kri.history.map((point) => point.value), 84, 26, color) : '<span class="small text-muted">Belum cukup data</span>') + '</div>'
                    + '<div class="grc-kri-status">' + (kri.stale ? '<span class="grc-pill is-muted">Basi</span>' : pill) + '</div>'
                    + '</div>';
            }).join('') + noDataNote;
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
        const largest = data.largest[0];
        body.innerHTML = '<div class="grc-stat-pair"><div>Total 12 bulan<b>' + esc(rupiah(data.total_amount)) + '</b></div>'
            + '<div>Kejadian<b>' + num(data.total_events) + '</b></div>'
            + (largest ? '<div' + tipAttr(esc(largest.code) + ' · ' + esc(largest.title) + '<br>' + esc(shortDate(largest.date))) + '>Terbesar<b><a href="' + esc(largest.url) + '">' + esc(rupiah(largest.amount)) + '</a></b></div>' : '')
            + '</div>'
            + '<div class="grc-chart sm"><canvas data-loss-chart role="img" aria-label="Loss event per bulan"></canvas></div>';

        if (typeof Chart === 'undefined') return;
        const last = data.series.length - 1;
        charts.losses = new Chart(body.querySelector('[data-loss-chart]'), {
            type: 'bar',
            data: {
                labels: data.series.map((point) => point.label),
                datasets: [{
                    label: 'Loss',
                    data: data.series.map((point) => point.amount),
                    backgroundColor: data.series.map((_, index) => index === last ? '#1e3a8a' : 'rgba(30, 58, 138, .5)'),
                    borderRadius: { topLeft: 4, topRight: 4 },
                    borderSkipped: 'bottom',
                    maxBarThickness: 22,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign(chartTooltip(), { callbacks: { label: (ctx) => rupiah(ctx.parsed.y) + ' · ' + data.series[ctx.dataIndex].events + ' kejadian' } }),
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#667382', font: { size: 11 }, maxRotation: 0, autoSkip: true } },
                    y: { beginAtZero: true, grid: { color: '#eef0f3', drawBorder: false }, ticks: { color: '#667382', font: { size: 11 }, maxTicksLimit: 5, callback: (value) => rupiah(value) } },
                },
            },
        });
    }

    // ------------------------------------------------------------------
    // H: overdue action age per unit + committee decisions
    // ------------------------------------------------------------------

    function renderActions(data, body) {
        const d = data.decisions;
        const aging = data.aging || { units: [], units_total: 0, totals: {} };
        let html = '';

        if (aging.units.length) {
            const max = Math.max(1, ...aging.units.map((unit) => unit.total));
            html += '<div class="grc-bars">' + aging.units.map((unit) => (
                '<div class="grc-bar-row"><span class="name" title="' + esc(unit.owner) + '">' + esc(unit.owner) + '</span>'
                + '<span><span class="grc-stack" style="width:' + (unit.total / max * 100) + '%" role="img" aria-label="' + esc(unit.owner + ': ' + AGING.map(([key, label]) => label + ' ' + unit.counts[key]).join(', ')) + '">'
                + AGING.filter(([key]) => unit.counts[key] > 0).map(([key, label]) => (
                    '<span class="age-' + key + '" style="flex:' + unit.counts[key] + '"' + tipAttr(esc(unit.owner) + ' · terlambat ' + label + ': ' + num(unit.counts[key]) + ' aksi') + '></span>'
                )).join('') + '</span></span><span class="total">' + num(unit.total) + '</span></div>'
            )).join('') + '</div>'
                + legend(AGING.map(([key, label], index) => ['var(--grc-age' + (index + 1) + ')', label + ' · ' + num(aging.totals[key] || 0)]))
                + (aging.units_total > aging.units.length ? '<div class="grc-card-sub mt-1">' + (aging.units_total - aging.units.length) + ' unit lain tidak ditampilkan.</div>' : '');
        } else {
            html += emptyState('Tidak ada aksi yang terlambat.', '');
        }

        const decisionTotal = d.open + d.overdue + d.closed;
        html += '<div class="grc-heat-title mt-3">Keputusan komite</div>';
        if (decisionTotal) {
            html += '<div class="grc-split" role="img" aria-label="Keputusan komite: ' + d.closed + ' selesai, ' + d.open + ' terbuka, ' + d.overdue + ' terlambat">'
                + [[d.closed, 'var(--grc-low)', 'selesai'], [d.open, 'var(--grc-faint)', 'terbuka'], [d.overdue, 'var(--grc-critical)', 'terlambat']]
                    .filter(([n]) => n > 0)
                    .map(([n, color, label]) => '<span style="flex:' + n + ';background:' + color + '"' + tipAttr(num(n) + ' keputusan ' + label) + '></span>').join('')
                + '</div>'
                + legend([['var(--grc-low)', 'Selesai ' + num(d.closed)], ['var(--grc-faint)', 'Terbuka ' + num(d.open)], ['var(--grc-critical)', 'Terlambat ' + num(d.overdue)]]);
        } else {
            html += '<div class="small text-muted">Belum ada keputusan komite.</div>';
        }

        body.innerHTML = html;
    }

    // ------------------------------------------------------------------
    // I: assurance timeline
    // ------------------------------------------------------------------

    const ASSURANCE_LANES = [['vendor', 'Review vendor'], ['bia', 'Review BIA'], ['bcp', 'Uji BCP']];
    const TIMELINE_PAST_DAYS = 30;

    function renderAssurance(data, body, host) {
        const counts = host.querySelector('[data-assurance-counts]');
        counts.innerHTML = data.items.length
            ? (data.counts.overdue ? '<span class="grc-pill is-critical">' + num(data.counts.overdue) + ' terlambat</span>' : '')
                + '<span class="grc-pill is-muted">' + num(data.counts.upcoming) + ' akan datang</span>'
            : '';

        if (!data.items.length) {
            body.innerHTML = emptyState('Tidak ada assurance yang jatuh tempo.', 'Dalam ' + data.horizon_days + ' hari ke depan semua jadwal review dan uji aman.');
            return;
        }

        const lo = -TIMELINE_PAST_DAYS, hi = data.horizon_days;
        const pct = (days) => clampPct(days, lo, hi);
        const today = new Date(); today.setHours(0, 0, 0, 0);
        const dateAt = (days) => { const d = new Date(today); d.setDate(d.getDate() + days); return dayMonth.format(d); };

        let html = '<div class="grc-tl">';
        ASSURANCE_LANES.forEach(([type, label]) => {
            const items = data.items.filter((item) => item.type === type);
            html += '<div class="grc-tl-label">' + esc(label) + ' <span class="text-muted">· ' + num(items.length) + '</span></div>'
                + '<div class="grc-tl-lane"><span class="grc-tl-past" style="width:' + pct(0) + '%"></span><span class="grc-tl-today" style="left:' + pct(0) + '%"></span>'
                + items.map((item) => {
                    let days = null, when = 'Belum pernah dilakukan';
                    if (item.due) {
                        days = Math.round((new Date(item.due + 'T00:00:00') - today) / 86400000);
                        when = days < 0 ? Math.abs(days) + ' hari lalu' : (days === 0 ? 'Hari ini' : days + ' hari lagi');
                    }
                    const position = days === null ? 0 : pct(days);
                    const color = item.overdue ? 'var(--grc-critical)' : 'var(--grc-medium)';
                    const tip = esc(item.label) + ' · ' + esc(item.subject) + '<br>' + (item.due ? esc(shortDate(item.due)) + ' · ' : '') + esc(when)
                        + (days !== null && days < lo ? '<br>(di luar rentang, ditaruh di tepi kiri)' : '');
                    return '<a class="grc-tl-dot' + (days === null ? ' never' : '') + '" href="' + esc(item.url) + '" style="left:' + position + '%;background:' + color + '"'
                        + tipAttr(tip) + ' aria-label="' + esc(item.label + ' ' + item.subject + ', ' + when) + '"></a>';
                }).join('')
                + '</div>';
        });
        html += '<div></div><div class="grc-tl-axis">'
            + [lo, -15, 0, 7, 14, 21, hi].map((d) => '<span style="left:' + pct(d) + '%">' + (d === 0 ? '<b class="text-body">Hari ini</b>' : esc(dateAt(d))) + '</span>').join('')
            + '</div></div>';

        body.innerHTML = html;
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
        body.innerHTML = '<div class="grc-chart"><canvas data-trend-chart role="img" aria-label="Tren postur risiko per akhir bulan"></canvas></div>'
            + '<div class="grc-card-sub mt-1">Snapshot pertama: ' + esc(shortDate(data.first_period)) + '</div>';

        if (typeof Chart === 'undefined') return;
        const last = data.series.length - 1;
        const endpoint = (ctx) => ctx.dataIndex === last ? 4 : 0;
        const line = (label, key, color, fill) => ({
            label, data: data.series.map((point) => point[key]),
            borderColor: color, backgroundColor: color, borderWidth: 2, tension: 0.25,
            pointRadius: endpoint, pointHoverRadius: 5, pointBorderColor: '#fff', pointBorderWidth: 2,
            fill: fill ? { target: 'origin', above: 'rgba(214, 57, 57, .08)' } : false,
        });

        charts.trend = new Chart(body.querySelector('[data-trend-chart]'), {
            type: 'line',
            data: {
                labels: data.series.map((point) => point.label),
                datasets: [
                    line('Risk tinggi (residual)', 'high_risks', '#f76707', false),
                    line('Melewati toleransi', 'breaches', '#d63939', true),
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'line', boxWidth: 18, color: '#667382', font: { size: 11 } } },
                    tooltip: Object.assign(chartTooltip(), {
                        callbacks: { footer: (items) => { const p = data.series[items[0].dataIndex]; return p.avg_residual !== null ? 'Rata-rata residual ' + p.avg_residual : ''; } },
                    }),
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#667382', font: { size: 11 } } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#667382', font: { size: 11 } }, grid: { color: '#eef0f3', drawBorder: false } },
                },
            },
        });
    }

    // ------------------------------------------------------------------

    load('summary', renderSummary);
    load('portfolio', renderPortfolio);
    load('heatmap', renderHeatmap);
    load('trend', renderTrend);
    load('kris', renderKris);
    load('top-risks', renderTopRisks);
    load('profile', renderProfile);
    load('losses', renderLosses);
    load('actions', renderActions);
    load('assurance', renderAssurance);
    load('attention', renderAttention);
})();
</script>
