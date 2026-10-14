<style>
    /* Main GRC dashboard. Neutral by default; colour is reserved for state. */
    .grc-dashboard {
        --grc-ink: #182433;
        --grc-muted: #667382;
        --grc-line: #e6e8ec;
        --grc-surface: #ffffff;
        --grc-soft: #f6f8fb;
        --grc-accent: #1e3a8a;
        --grc-critical: #d63939;
        --grc-high: #f76707;
        --grc-medium: #f59f00;
        --grc-low-mod: #74b816;
        --grc-low: #2fb344;
        color: var(--grc-ink);
    }

    /* Toolbar */
    .grc-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1rem; }
    .grc-toolbar-left, .grc-toolbar-right { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
    .grc-tabs { display: inline-flex; gap: .25rem; padding: .2rem; background: var(--grc-soft); border: 1px solid var(--grc-line); border-radius: .5rem; }
    .grc-tab { padding: .35rem .85rem; border-radius: .375rem; font-weight: 500; font-size: .85rem; color: var(--grc-muted); text-decoration: none; }
    .grc-tab:hover { color: var(--grc-ink); text-decoration: none; }
    .grc-tab.active { background: var(--grc-surface); color: var(--grc-ink); box-shadow: 0 1px 2px rgba(24, 36, 51, .08); }
    .grc-asof { font-size: .78rem; color: var(--grc-muted); }
    .grc-asof .grc-warn-text { color: #b35900; }
    .grc-filters { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; }
    .grc-filters .form-select { width: auto; max-width: 220px; }
    /* The layout turns every select into Select2 at 100% width; keep the toolbar compact. */
    .grc-filters .select2-container { width: 200px !important; }

    /* Section shell */
    .grc-card { height: 100%; }
    .grc-card .card-header { gap: .5rem; flex-wrap: wrap; }
    .grc-card .card-title { font-weight: 600; }
    .grc-card-sub { font-size: .78rem; color: var(--grc-muted); }
    .grc-state { padding: 1.5rem 1rem; text-align: center; color: var(--grc-muted); font-size: .85rem; }
    .grc-state strong { display: block; color: var(--grc-ink); font-weight: 600; margin-bottom: .2rem; }
    .grc-state .btn { margin-top: .6rem; }

    /* Status tiles */
    .grc-tile { display: flex; flex-direction: column; gap: .2rem; width: 100%; height: 100%; text-align: left; padding: 1rem 1.1rem; background: var(--grc-surface); border: 1px solid var(--grc-line); border-radius: .5rem; color: inherit; position: relative; }
    button.grc-tile { cursor: pointer; }
    button.grc-tile:hover { border-color: #c5cad3; }
    button.grc-tile:focus-visible { outline: 2px solid var(--grc-accent); outline-offset: 2px; }
    .grc-tile[aria-pressed="true"] { border-color: var(--grc-accent); box-shadow: inset 0 0 0 1px var(--grc-accent); }
    .grc-tile::before { content: ""; position: absolute; left: 0; top: .75rem; bottom: .75rem; width: 3px; border-radius: 0 3px 3px 0; background: transparent; }
    .grc-tile[data-state="critical"]::before { background: var(--grc-critical); }
    .grc-tile[data-state="warning"]::before { background: var(--grc-medium); }
    .grc-tile-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--grc-muted); font-weight: 500; }
    .grc-tile-value { font-size: 1.65rem; font-weight: 600; line-height: 1.15; font-variant-numeric: tabular-nums; }
    .grc-tile-detail { font-size: .8rem; color: var(--grc-muted); }
    .grc-tile-compare { font-size: .78rem; color: var(--grc-muted); margin-top: auto; padding-top: .35rem; }
    .grc-delta-up { color: var(--grc-critical); font-weight: 500; }
    .grc-delta-down { color: var(--grc-low); font-weight: 500; }

    /* Attention list */
    .grc-chips { display: flex; flex-wrap: wrap; gap: .3rem; }
    .grc-chip { border: 1px solid var(--grc-line); background: var(--grc-surface); color: var(--grc-muted); border-radius: 999px; padding: .1rem .6rem; font-size: .75rem; }
    .grc-chip[aria-pressed="true"] { background: var(--grc-accent); border-color: var(--grc-accent); color: #fff; }
    .grc-attn { list-style: none; margin: 0; padding: 0; max-height: 470px; overflow-y: auto; }
    .grc-attn li { display: grid; grid-template-columns: 4px 1fr auto; gap: .75rem; padding: .65rem 1rem; border-bottom: 1px solid var(--grc-line); align-items: center; }
    .grc-attn li:last-child { border-bottom: 0; }
    .grc-attn .sev { align-self: stretch; border-radius: 2px; }
    .grc-attn .sev-critical { background: var(--grc-critical); }
    .grc-attn .sev-high { background: var(--grc-high); }
    .grc-attn .sev-medium { background: var(--grc-medium); }
    .grc-attn a.title { color: var(--grc-ink); font-weight: 500; font-size: .875rem; line-height: 1.3; }
    .grc-attn .meta { font-size: .76rem; color: var(--grc-muted); margin-top: .1rem; }
    .grc-attn .age { font-size: .75rem; white-space: nowrap; font-variant-numeric: tabular-nums; }

    /* Heatmaps */
    .grc-heat-pair { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    .grc-heat-title { font-size: .78rem; font-weight: 600; margin-bottom: .35rem; }
    .grc-heat { display: grid; grid-template-columns: 1.1rem repeat(5, minmax(0, 1fr)); gap: 3px; }
    .grc-heat .axis { font-size: .65rem; color: var(--grc-muted); display: flex; align-items: center; justify-content: center; }
    .grc-heat button { aspect-ratio: 1; border: 0; border-radius: 3px; font-size: .78rem; font-weight: 600; font-variant-numeric: tabular-nums; padding: 0; color: #fff; }
    .grc-heat button.empty { opacity: .28; cursor: default; }
    .grc-heat button:not(.empty):hover { outline: 2px solid var(--grc-ink); outline-offset: 1px; }
    .grc-heat button[aria-pressed="true"] { outline: 2px solid var(--grc-ink); outline-offset: 1px; }
    .lvl-High { background: var(--grc-critical); }
    .lvl-Moderate-to-High { background: var(--grc-high); }
    .lvl-Moderate { background: var(--grc-medium); color: #3b2a00 !important; }
    .lvl-Low-to-Moderate { background: var(--grc-low-mod); color: #1f3300 !important; }
    .lvl-Low { background: var(--grc-low); }
    .lvl-Belum-dinilai { background: #c5cad3; }
    .grc-heat-axis-label { font-size: .68rem; color: var(--grc-muted); text-align: center; margin-top: .25rem; }
    .grc-heat-legend { display: flex; flex-wrap: wrap; gap: .6rem; font-size: .72rem; color: var(--grc-muted); margin-top: .75rem; }
    .grc-heat-legend span::before { content: ""; display: inline-block; width: .65rem; height: .65rem; border-radius: 2px; margin-right: .3rem; vertical-align: -1px; background: var(--swatch); }
    .grc-cell-detail { margin-top: .9rem; border-top: 1px solid var(--grc-line); padding-top: .75rem; }

    /* Stacked bars (profile, actions) */
    .grc-bars { display: grid; gap: .7rem; }
    .grc-bar-row { display: grid; grid-template-columns: minmax(0, 9.5rem) 1fr 2.5rem; gap: .6rem; align-items: center; font-size: .8rem; }
    .grc-bar-row .name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .grc-bar-row .total { text-align: right; color: var(--grc-muted); font-variant-numeric: tabular-nums; }
    .grc-stack { display: flex; height: .8rem; border-radius: 3px; overflow: hidden; background: var(--grc-soft); }
    .grc-stack span { height: 100%; }
    .seg-completed { background: var(--grc-low); }
    .seg-on_track { background: #9aa6b2; }
    .seg-blocked { background: var(--grc-medium); }
    .seg-overdue { background: var(--grc-critical); }

    /* Tables */
    .grc-table td, .grc-table th { vertical-align: middle; }
    .grc-table .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
    .grc-score { display: inline-flex; align-items: center; gap: .3rem; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .grc-score .arrow { color: var(--grc-muted); font-size: .7rem; }
    .grc-spark { width: 96px; height: 28px; display: block; }
    .grc-progress { font-size: .75rem; color: var(--grc-muted); white-space: nowrap; }

    .grc-chart { position: relative; height: 210px; }
    .grc-largest { font-size: .8rem; }
    .grc-largest li { display: flex; justify-content: space-between; gap: .75rem; padding: .35rem 0; border-bottom: 1px dashed var(--grc-line); }
    .grc-largest li:last-child { border-bottom: 0; }

    @media (max-width: 575.98px) {
        .grc-heat-pair { grid-template-columns: 1fr; }
        .grc-filters .form-select { max-width: 100%; flex: 1 1 140px; }
        .grc-filters .select2-container { width: 100% !important; }
        .grc-bar-row { grid-template-columns: minmax(0, 6.5rem) 1fr 2rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        .grc-dashboard .placeholder-glow .placeholder { animation: none; }
    }
</style>
