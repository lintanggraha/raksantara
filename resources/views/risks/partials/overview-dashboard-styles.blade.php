<style>
    /* Main GRC dashboard. Neutral by default; colour is reserved for state. */
    .grc-dashboard {
        --grc-ink: #182433;
        --grc-muted: #667382;
        --grc-faint: #9aa6b2;
        --grc-line: #e6e8ec;
        --grc-surface: #ffffff;
        --grc-soft: #f6f8fb;
        --grc-accent: #1e3a8a;
        --grc-critical: #d63939;
        --grc-high: #f76707;
        --grc-medium: #f59f00;
        --grc-low-mod: #74b816;
        --grc-low: #2fb344;
        /* Overdue age: ordinal ramp from "recently late" to "long overdue". */
        --grc-age1: #f5c26b;
        --grc-age2: #f59f00;
        --grc-age3: #f76707;
        --grc-age4: #d63939;
        color: var(--grc-ink);
    }

    /* Toolbar */
    .grc-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1rem; }
    .grc-toolbar-left, .grc-toolbar-right { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
    .grc-tabs { display: inline-flex; gap: .25rem; padding: .2rem; background: var(--grc-soft); border: 1px solid var(--grc-line); border-radius: .5rem; }
    .grc-tab { padding: .35rem .85rem; border-radius: .375rem; font-weight: 500; font-size: .85rem; color: var(--grc-muted); text-decoration: none; }
    .grc-tab:hover { color: var(--grc-ink); text-decoration: none; }
    .grc-tab.active { background: var(--grc-surface); color: var(--grc-ink); box-shadow: 0 1px 2px rgba(24, 36, 51, .08); }
    .grc-asof { font-size: .78rem; color: var(--grc-muted); display: inline-flex; flex-wrap: wrap; align-items: center; gap: .35rem; }
    .grc-filters { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; }
    .grc-filters .form-select { width: auto; max-width: 220px; }
    /* The layout turns every select into Select2 at 100% width; keep the toolbar compact. */
    .grc-filters .select2-container { width: 200px !important; }

    /* Section shell */
    .grc-card { height: 100%; }
    .grc-card .card-header { gap: .5rem; flex-wrap: wrap; }
    .grc-card .card-title { font-weight: 600; }
    .grc-card-sub { font-size: .78rem; color: var(--grc-muted); }
    .grc-info { width: 18px; height: 18px; padding: 0; border-radius: 50%; border: 1px solid var(--grc-line); background: var(--grc-soft); color: var(--grc-muted); font-size: .68rem; font-weight: 600; line-height: 1; display: inline-grid; place-items: center; cursor: help; }
    .grc-state { padding: 1.5rem 1rem; text-align: center; color: var(--grc-muted); font-size: .85rem; }
    .grc-state strong { display: block; color: var(--grc-ink); font-weight: 600; margin-bottom: .2rem; }
    .grc-state .btn { margin-top: .6rem; }
    .grc-seg { display: inline-flex; border: 1px solid var(--grc-line); border-radius: .375rem; overflow: hidden; }
    .grc-seg button { border: 0; background: var(--grc-surface); padding: .2rem .65rem; font-size: .75rem; color: var(--grc-muted); }
    .grc-seg button[aria-pressed="true"] { background: var(--grc-accent); color: #fff; }

    /* Pills */
    .grc-pill { display: inline-flex; align-items: center; gap: .3rem; padding: .05rem .5rem; border-radius: 999px; font-size: .72rem; font-weight: 500; white-space: nowrap; border: 1px solid var(--grc-line); background: var(--grc-soft); color: var(--grc-ink); }
    .grc-pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--grc-faint); }
    .grc-pill.is-critical { background: #fbeaea; border-color: #f2c2c2; }
    .grc-pill.is-critical::before { background: var(--grc-critical); }
    .grc-pill.is-warning { background: #fef4e0; border-color: #f8d99a; }
    .grc-pill.is-warning::before { background: var(--grc-medium); }
    .grc-pill.is-ok { background: #e9f6ec; border-color: #b9e2c2; }
    .grc-pill.is-ok::before { background: var(--grc-low); }
    .grc-pill.is-muted { color: var(--grc-muted); }

    /* Status tiles */
    .grc-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1rem; }
    .grc-tile { display: grid; grid-template-columns: minmax(0, 1fr) auto; grid-template-rows: auto auto auto; column-gap: .5rem; align-items: end; width: 100%; text-align: left; padding: .8rem .95rem .7rem; background: var(--grc-surface); border: 1px solid var(--grc-line); border-radius: .5rem; color: inherit; position: relative; }
    .grc-tile.placeholder-glow { display: flex; flex-direction: column; gap: .35rem; align-items: stretch; }
    button.grc-tile { cursor: pointer; }
    button.grc-tile:hover { border-color: #c5cad3; }
    button.grc-tile:focus-visible { outline: 2px solid var(--grc-accent); outline-offset: 2px; }
    .grc-tile[aria-pressed="true"] { border-color: var(--grc-accent); box-shadow: inset 0 0 0 1px var(--grc-accent); }
    .grc-tile::before { content: ""; position: absolute; left: 0; top: .75rem; bottom: .75rem; width: 3px; border-radius: 0 3px 3px 0; background: transparent; }
    .grc-tile[data-state="critical"]::before { background: var(--grc-critical); }
    .grc-tile[data-state="warning"]::before { background: var(--grc-medium); }
    .grc-tile-label { grid-column: 1 / -1; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--grc-muted); font-weight: 500; }
    .grc-tile-value { font-size: 1.65rem; font-weight: 600; line-height: 1.15; margin-top: .2rem; white-space: nowrap; }
    .grc-tile-value small { font-size: 1rem; font-weight: 500; color: var(--grc-muted); }
    .grc-tile-visual { grid-column: 2; grid-row: 2 / 4; align-self: end; }
    .grc-tile-compare { font-size: .75rem; color: var(--grc-muted); margin-top: .2rem; white-space: nowrap; }
    .grc-tile-mix { display: flex; gap: 2px; width: 84px; height: 10px; margin-bottom: .35rem; }
    .grc-tile-mix span { height: 100%; }
    .grc-tile-mix span:first-child { border-radius: 3px 0 0 3px; }
    .grc-tile-mix span:last-child { border-radius: 0 3px 3px 0; }
    .grc-delta-up { color: var(--grc-critical); font-weight: 600; }
    .grc-delta-down { color: var(--grc-low); font-weight: 600; }

    /* Follow-up drawer */
    .grc-chips { display: flex; flex-wrap: wrap; gap: .3rem; }
    .grc-chip { border: 1px solid var(--grc-line); background: var(--grc-surface); color: var(--grc-muted); border-radius: 999px; padding: .1rem .6rem; font-size: .75rem; }
    .grc-chip[aria-pressed="true"] { background: var(--grc-accent); border-color: var(--grc-accent); color: #fff; }
    .grc-attn { list-style: none; margin: 0; padding: 0; max-height: 520px; overflow-y: auto; }
    .grc-attn li { display: grid; grid-template-columns: 4px 1fr auto; gap: .75rem; padding: .65rem 1rem; border-bottom: 1px solid var(--grc-line); align-items: center; }
    .grc-attn li:last-child { border-bottom: 0; }
    .grc-attn .sev { align-self: stretch; border-radius: 2px; }
    .grc-attn .sev-critical { background: var(--grc-critical); }
    .grc-attn .sev-high { background: var(--grc-high); }
    .grc-attn .sev-medium { background: var(--grc-medium); }
    .grc-attn a.title { color: var(--grc-ink); font-weight: 500; font-size: .875rem; line-height: 1.3; }
    .grc-attn .meta { font-size: .76rem; color: var(--grc-muted); margin-top: .1rem; }
    .grc-attn .age { font-size: .75rem; white-space: nowrap; font-variant-numeric: tabular-nums; }

    /* Heatmap */
    .grc-heat-wrap { display: grid; grid-template-columns: 1rem minmax(0, 1fr); gap: .4rem; max-width: 400px; margin: 0 auto; }
    .grc-heat-ylab { writing-mode: vertical-rl; transform: rotate(180deg); font-size: .68rem; color: var(--grc-muted); text-align: center; }
    .grc-heat-title { font-size: .78rem; font-weight: 600; margin-bottom: .35rem; }
    .grc-heat { display: grid; grid-template-columns: 1rem repeat(5, minmax(0, 1fr)); gap: 3px; }
    .grc-heat .axis { font-size: .68rem; color: var(--grc-muted); display: flex; align-items: center; justify-content: center; }
    .grc-heat button { aspect-ratio: 1; border: 0; border-radius: 4px; font-size: .95rem; font-weight: 600; font-variant-numeric: tabular-nums; padding: 0; color: #fff; }
    .grc-heat button.empty { opacity: .22; cursor: default; }
    .grc-heat button:not(.empty):hover { outline: 2px solid var(--grc-ink); outline-offset: 1px; }
    .grc-heat button[aria-pressed="true"] { outline: 2px solid var(--grc-ink); outline-offset: 1px; }
    .lvl-High { background: var(--grc-critical); }
    .lvl-Moderate-to-High { background: var(--grc-high); }
    .lvl-Moderate { background: var(--grc-medium); color: #3b2a00 !important; }
    .lvl-Low-to-Moderate { background: var(--grc-low-mod); color: #1f3300 !important; }
    .lvl-Low { background: var(--grc-low); }
    .lvl-Belum-dinilai { background: #c5cad3; }
    .grc-heat-axis-label { font-size: .68rem; color: var(--grc-muted); text-align: center; margin-top: .25rem; }
    .grc-heat-legend { display: flex; flex-wrap: wrap; gap: .4rem .9rem; font-size: .72rem; color: var(--grc-muted); margin-top: .75rem; }
    .grc-heat-legend.center { justify-content: center; }
    .grc-heat-legend span::before { content: ""; display: inline-block; width: .65rem; height: .65rem; border-radius: 2px; margin-right: .3rem; vertical-align: -1px; background: var(--swatch); }
    .grc-heat-legend .ring::before { border-radius: 50%; background: var(--grc-surface); box-shadow: inset 0 0 0 2px var(--grc-faint); }
    .grc-heat-legend .dot::before { border-radius: 50%; }
    .grc-heat-legend .tick::before { width: 2px; height: .8rem; vertical-align: -2px; background: var(--grc-ink); }
    .grc-cell-detail { margin-top: .9rem; border-top: 1px solid var(--grc-line); padding-top: .75rem; }

    /* Stacked bars (profile, aging, decisions) */
    .grc-bars { display: grid; gap: .6rem; }
    .grc-bar-row { display: grid; grid-template-columns: minmax(0, 8rem) 1fr 2rem; gap: .6rem; align-items: center; font-size: .8rem; }
    .grc-bar-row .name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .grc-bar-row .total { text-align: right; color: var(--grc-muted); font-variant-numeric: tabular-nums; }
    .grc-stack { display: flex; gap: 2px; height: .85rem; }
    .grc-stack span { height: 100%; }
    .grc-stack span:last-child { border-radius: 0 4px 4px 0; }
    .grc-split { display: flex; gap: 2px; height: .85rem; }
    .grc-split span { height: 100%; }
    .grc-split span:first-child { border-radius: 4px 0 0 4px; }
    .grc-split span:last-child { border-radius: 0 4px 4px 0; }
    .age-d30 { background: var(--grc-age1); }
    .age-d60 { background: var(--grc-age2); }
    .age-d90 { background: var(--grc-age3); }
    .age-d90_plus { background: var(--grc-age4); }

    /* Bullet graphs (KRI) */
    .grc-kri-head, .grc-kri-row { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1.6fr) 4.5rem 5.5rem 4.2rem; gap: .75rem; align-items: center; }
    .grc-kri-head { font-size: .68rem; color: var(--grc-muted); text-transform: uppercase; letter-spacing: .04em; padding-bottom: .3rem; border-bottom: 1px solid var(--grc-line); }
    .grc-kri-head span:nth-child(3), .grc-kri-head span:nth-child(5) { text-align: right; }
    .grc-kri-row { padding: .45rem 0; border-bottom: 1px solid var(--grc-line); }
    .grc-kri-row:last-child { border-bottom: 0; }
    .grc-kri-name { min-width: 0; font-size: .82rem; font-weight: 500; }
    .grc-kri-name a { color: var(--grc-ink); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .grc-kri-name .meta { display: block; font-weight: 400; font-size: .7rem; color: var(--grc-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .grc-kri-value { text-align: right; font-size: .82rem; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .grc-kri-status { text-align: right; }
    .grc-bullet { position: relative; height: 16px; border-radius: 3px; overflow: hidden; background: var(--grc-soft); }
    .grc-bullet .band { position: absolute; top: 0; bottom: 0; }
    .grc-bullet .band-ok { background: #dff2e3; }
    .grc-bullet .band-warn { background: #fdecc8; }
    .grc-bullet .band-crit { background: #f7d6d6; }
    .grc-bullet .val { position: absolute; top: 5px; height: 6px; left: 0; background: var(--grc-ink); border-radius: 0 3px 3px 0; }
    .grc-bullet .mark { position: absolute; top: 3px; bottom: 3px; width: 2px; margin-left: -1px; background: var(--grc-ink); opacity: .55; }
    .grc-bullet.lg { height: 26px; border-radius: 4px; }
    .grc-bullet.lg .val { top: 9px; height: 8px; }
    .grc-bullet-axis { position: relative; height: 1rem; font-size: .68rem; color: var(--grc-muted); margin-top: .25rem; }
    .grc-bullet-axis span { position: absolute; transform: translateX(-50%); font-variant-numeric: tabular-nums; }
    .grc-spark { display: block; }
    @media (max-width: 575.98px) {
        .grc-kri-head, .grc-kri-row { grid-template-columns: minmax(0, 1fr) 4rem 4rem; }
        .grc-kri-row .grc-bullet { grid-column: 1 / -1; grid-row: 2; }
        .grc-kri-row .spark-cell, .grc-kri-head span:nth-child(2), .grc-kri-head span:nth-child(4) { display: none; }
    }

    /* Dumbbell (top risks, control effectiveness) */
    .grc-db-row, .grc-db-axis { display: grid; grid-template-columns: minmax(0, 10.5rem) minmax(0, 1fr) 2.8rem; gap: .6rem; align-items: center; }
    .grc-db-row { padding: .3rem 0; }
    .grc-db-row .lbl { font-size: .78rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .grc-db-row .lbl a { color: var(--grc-ink); }
    .grc-db-row .lbl .code { color: var(--grc-muted); font-size: .72rem; margin-right: .25rem; }
    .grc-db-track { position: relative; height: 22px; background-image: linear-gradient(to right, var(--grc-line) 1px, transparent 1px); background-size: 20% 100%; border-right: 1px solid var(--grc-line); }
    .grc-db-line { position: absolute; top: 10px; height: 2px; background: var(--grc-faint); border-radius: 1px; }
    .grc-db-dot { position: absolute; top: 5px; width: 12px; height: 12px; margin-left: -6px; border-radius: 50%; box-shadow: 0 0 0 2px var(--grc-surface); }
    .grc-db-dot.inherent { background: var(--grc-surface); border: 2px solid var(--grc-faint); }
    .grc-db-tick { position: absolute; top: 1px; width: 2px; height: 20px; margin-left: -1px; background: var(--grc-ink); border-radius: 1px; }
    .grc-db-delta { font-size: .75rem; text-align: right; font-variant-numeric: tabular-nums; color: var(--grc-muted); white-space: nowrap; }
    .grc-db-delta.is-critical { color: var(--grc-critical); font-weight: 600; }
    .grc-db-delta.is-warning { color: #b35900; font-weight: 600; }
    .grc-db-axis { font-size: .68rem; color: var(--grc-muted); }
    .grc-db-axis .ticks { position: relative; height: 1rem; }
    .grc-db-axis .ticks span { position: absolute; transform: translateX(-50%); font-variant-numeric: tabular-nums; }
    @media (max-width: 575.98px) { .grc-db-row, .grc-db-axis { grid-template-columns: minmax(0, 6rem) minmax(0, 1fr) 2.5rem; } }

    /* Portfolio at a glance */
    .grc-portfolio-head { display: flex; align-items: center; gap: .9rem; margin-bottom: .9rem; }
    .grc-portfolio-sub { font-size: .78rem; color: var(--grc-muted); line-height: 1.35; }
    .grc-portfolio-sub b { color: var(--grc-ink); font-weight: 600; }
    .grc-bar-row.is-other .name { color: var(--grc-muted); font-style: italic; }

    /* Ranked bars (top risks): residual over a faded inherent bar, appetite line */
    .grc-rank { display: grid; gap: .55rem; }
    .grc-rank-row { display: grid; gap: .2rem; }
    .grc-rank-head { display: flex; align-items: center; gap: .5rem; min-width: 0; }
    .grc-rank-title { flex: 1; min-width: 0; font-size: .8rem; font-weight: 500; color: var(--grc-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .grc-rank-title .code { color: var(--grc-muted); font-weight: 400; font-size: .72rem; }
    .grc-rank-body { display: grid; grid-template-columns: minmax(0, 1fr) 4.2rem; gap: .6rem; align-items: center; }
    .grc-rank-track { position: relative; height: 12px; }
    .grc-rank-inherent, .grc-rank-residual { position: absolute; left: 0; border-radius: 0 4px 4px 0; }
    .grc-rank-inherent { top: 0; height: 12px; background: #dfe3e9; }
    .grc-rank-residual { top: 2px; height: 8px; }
    .grc-rank-appetite { position: absolute; top: -4px; height: 20px; width: 2px; margin-left: -1px; background: var(--grc-ink); border-radius: 1px; box-shadow: 0 0 0 1px var(--grc-surface); }
    .grc-rank-score { font-size: .75rem; color: var(--grc-muted); white-space: nowrap; font-variant-numeric: tabular-nums; }
    .grc-rank-score b { font-size: .85rem; color: var(--grc-ink); font-weight: 600; }

    /* Assurance timeline */
    .grc-tl { display: grid; grid-template-columns: 8rem minmax(0, 1fr); column-gap: .75rem; align-items: center; }
    .grc-tl-label { font-size: .8rem; color: var(--grc-muted); padding: .45rem 0; }
    .grc-tl-lane { position: relative; height: 34px; border-bottom: 1px solid var(--grc-line); }
    .grc-tl-past { position: absolute; top: 0; bottom: 0; left: 0; background: #fcf1f1; }
    .grc-tl-today { position: absolute; top: -4px; bottom: -4px; width: 2px; margin-left: -1px; background: var(--grc-ink); z-index: 1; }
    .grc-tl-dot { position: absolute; top: 11px; width: 12px; height: 12px; margin-left: -6px; border-radius: 50%; box-shadow: 0 0 0 2px var(--grc-surface); z-index: 2; display: block; }
    .grc-tl-dot.never { border-radius: 2px; }
    .grc-tl-axis { position: relative; height: 1rem; font-size: .68rem; color: var(--grc-muted); margin-top: .2rem; }
    .grc-tl-axis span { position: absolute; transform: translateX(-50%); white-space: nowrap; font-variant-numeric: tabular-nums; }
    @media (max-width: 575.98px) { .grc-tl { grid-template-columns: 5.5rem minmax(0, 1fr); } }

    /* Level migration matrix (analytical) */
    .grc-mig { display: grid; grid-template-columns: minmax(0, 6.5rem) repeat(5, minmax(2.6rem, 1fr)); gap: 3px; font-size: .78rem; min-width: 340px; }
    .grc-mig .hd { color: var(--grc-muted); font-size: .68rem; display: flex; align-items: center; justify-content: center; text-align: center; line-height: 1.2; padding: 2px; }
    .grc-mig .rh { justify-content: flex-start; text-align: left; }
    .grc-mig .c { aspect-ratio: 1.4; border-radius: 4px; display: grid; place-items: center; font-weight: 600; font-variant-numeric: tabular-nums; }
    .grc-mig .c.diag { box-shadow: inset 0 0 0 1.5px var(--grc-faint); }
    .grc-mig .c.worse { box-shadow: inset 0 0 0 2px var(--grc-critical); }

    /* Horizontal bars (analytical) */
    .grc-hbar-row { display: grid; grid-template-columns: minmax(0, 7rem) minmax(0, 1fr); gap: .6rem; align-items: center; font-size: .8rem; }
    .grc-hbar-row .name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .grc-hbar-cell { display: flex; align-items: center; gap: .5rem; }
    .grc-hbar { height: .85rem; background: var(--grc-accent); border-radius: 0 4px 4px 0; min-width: 2px; }
    .grc-hbar-cell b { font-size: .75rem; font-weight: 600; font-variant-numeric: tabular-nums; }

    .grc-recs { display: grid; gap: .75rem; }
    .grc-rec { display: grid; grid-template-columns: 4px minmax(0, 1fr); gap: .75rem; }
    .grc-rec .sev { border-radius: 2px; }
    .grc-rec b { display: block; font-size: .85rem; font-weight: 600; }
    .grc-rec p { margin: .1rem 0 0; font-size: .8rem; color: var(--grc-muted); max-width: 70ch; }
    .grc-big { font-size: 2.4rem; font-weight: 600; line-height: 1; }

    .grc-chart { position: relative; height: 230px; }
    .grc-chart.sm { height: 190px; }
    .grc-stat-pair { display: flex; gap: 1.5rem; margin-bottom: .5rem; font-size: .75rem; color: var(--grc-muted); }
    .grc-stat-pair b { display: block; font-size: 1.05rem; color: var(--grc-ink); font-weight: 600; }

    /* Tooltip (appended to body, so it cannot use the scoped tokens) */
    .grc-tip { position: fixed; z-index: 1080; pointer-events: none; background: #182433; color: #fff; font-size: .75rem; line-height: 1.4; padding: .35rem .55rem; border-radius: .375rem; max-width: 300px; box-shadow: 0 4px 14px rgba(0, 0, 0, .18); }

    @media (max-width: 575.98px) {
        .grc-filters .form-select { max-width: 100%; flex: 1 1 140px; }
        .grc-filters .select2-container { width: 100% !important; }
        .grc-bar-row { grid-template-columns: minmax(0, 6.5rem) 1fr 2rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        .grc-dashboard .placeholder-glow .placeholder { animation: none; }
    }
</style>
