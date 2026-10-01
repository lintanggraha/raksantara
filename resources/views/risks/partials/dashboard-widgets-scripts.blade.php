<script>
(function () {
    const dashboardRoot = document.getElementById('riskDashboardRoot');
    if (!dashboardRoot) {
        return;
    }

    const summaryUrl = dashboardRoot.dataset.summaryUrl;
    const preferenceGetUrl = dashboardRoot.dataset.prefGetUrl || '';
    const preferenceSaveUrl = dashboardRoot.dataset.prefSaveUrl || '';
    const refreshMs = Number(dashboardRoot.dataset.refreshMs || 30000);
    const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    const widgetGrid = document.getElementById('dashboardWidgetGrid');
    const visibilityKey = 'risk_dashboard_widget_visibility_v1';
    const orderKey = 'risk_dashboard_widget_order_v1';
    const dashboardTabKey = 'risk_dashboard_active_tab_v2';
    const statusColorMap = {
        Open: '#dc2626',
        Monitoring: '#f59e0b',
        Closed: '#16a34a',
        Unknown: '#64748b',
    };
    const availableDashboardTabs = ['exec_snapshot', 'ops_control', 'risk_intelligence'];
    const defaultDashboardTab = 'ops_control';
    const defaultChartColors = ['#2563eb', '#14b8a6', '#8b5cf6', '#f97316', '#0ea5e9', '#94a3b8'];
    let latestDashboardSummary = null;
    let preferenceSyncTimer = null;
    let activeDashboardTab = defaultDashboardTab;

    function clampPercent(value) {
        const number = Number(value);
        if (!Number.isFinite(number)) {
            return 0;
        }

        return Math.max(0, Math.min(100, number));
    }

    function escapeHtml(value) {
        const source = String(value ?? '-');
        return source
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNumber(value, decimals = 0) {
        const number = Number(value);
        const safeValue = Number.isFinite(number) ? number : 0;
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(safeValue);
    }

    function normalizeDashboardTab(value) {
        const normalized = String(value || '').toLowerCase().trim();
        if (availableDashboardTabs.indexOf(normalized) >= 0) {
            return normalized;
        }

        return defaultDashboardTab;
    }

    function normalizeCountRows(rows) {
        if (!Array.isArray(rows)) {
            return [];
        }

        return rows.map(function (row) {
            const count = Number(row && row.count ? row.count : 0);
            return {
                label: row && row.label ? String(row.label) : '-',
                count: Number.isFinite(count) ? count : 0,
            };
        }).filter(function (row) {
            return row.count > 0;
        });
    }

    function colorByLabel(label, index) {
        if (statusColorMap[label]) {
            return statusColorMap[label];
        }

        return defaultChartColors[index % defaultChartColors.length];
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (!element) {
            return;
        }
        element.textContent = value;
    }

    function renderSourceReference(containerId, reference) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }

        const raw = String(reference || '').trim();
        if (!raw || raw === '-') {
            container.textContent = '-';
            return;
        }

        const match = raw.match(/https?:\/\/[^\s]+/i);
        if (!match || !match[0]) {
            container.textContent = raw;
            return;
        }

        const url = String(match[0]).replace(/[),.;]+$/g, '');
        const note = raw.replace(match[0], '').trim();

        container.innerHTML = '';

        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.className = 'fw-semibold text-decoration-none';
        link.textContent = 'Lihat Referensi';
        container.appendChild(link);

        if (note) {
            const noteElement = document.createElement('div');
            noteElement.className = 'small text-muted mt-1';
            noteElement.textContent = note;
            container.appendChild(noteElement);
        }
    }

    function setProgressBar(id, value) {
        const bar = document.getElementById(id);
        if (!bar) {
            return;
        }

        const width = clampPercent(value);
        bar.style.width = width.toFixed(2) + '%';
    }

    function renderCountRows(bodyId, rows, emptyMessage = 'No data') {
        const body = document.getElementById(bodyId);
        if (!body) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="2" class="text-center text-muted">' + emptyMessage + '</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (row) {
            const label = escapeHtml(row && row.label ? row.label : '-');
            const count = formatNumber(row && row.count ? row.count : 0, 0);
            return '<tr><td>' + label + '</td><td class="text-end">' + count + '</td></tr>';
        }).join('');
    }

    function renderVarianceRows(rows) {
        const body = document.getElementById('varianceAnalysisBody');
        if (!body) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No variance data</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (row) {
            return [
                '<tr>',
                '<td>', escapeHtml(row && row.category ? row.category : '-'), '</td>',
                '<td class="text-end">', formatNumber(row && row.risk_count ? row.risk_count : 0, 0), '</td>',
                '<td class="text-end">', formatNumber(row && row.avg_inherent ? row.avg_inherent : 0, 2), '</td>',
                '<td class="text-end">', formatNumber(row && row.avg_residual ? row.avg_residual : 0, 2), '</td>',
                '<td class="text-end">', formatNumber(row && row.variance ? row.variance : 0, 2), '</td>',
                '</tr>'
            ].join('');
        }).join('');
    }

    function renderBenchmarkScoring(benchmark) {
        const payload = benchmark && typeof benchmark === 'object' ? benchmark : {};

        setText('benchmarkInternalAvg', formatNumber(payload.internal_average_score || 0, 2));
        setText('benchmarkIndustry', formatNumber(payload.industry_baseline_score || 0, 2));
        setText('benchmarkTarget', formatNumber(payload.target_score || 0, 2));
        setText('benchmarkGapIndustry', formatNumber(payload.gap_vs_industry || 0, 2));
        setText('benchmarkGapTarget', formatNumber(payload.gap_vs_target || 0, 2));
        setText('benchmarkPosition', payload.position_label || '-');
        setText('benchmarkSourceName', payload.source_name || '-');
        setText('benchmarkSourceYear', payload.source_year ? String(payload.source_year) : '-');
        renderSourceReference('benchmarkSourceReference', payload.source_reference || '-');
        setText(
            'benchmarkSourceInline',
            (payload.source_name || '-') + ' (' + (payload.source_year ? String(payload.source_year) : '-') + ')'
        );
        renderSourceReference('benchmarkSourceReferenceInline', payload.source_reference || '-');
        setText(
            'benchmarkMethodology',
            payload.methodology || 'Internal Avg is calculated from overall score (Residual fallback to Inherent).'
        );
        setText(
            'benchmarkMethodologyInline',
            payload.methodology || 'Internal Avg is calculated from overall score (Residual fallback to Inherent).'
        );
        setText('benchmarkReason', payload.position_reason || '-');
        setText('benchmarkReasonInline', payload.position_reason || '-');
    }

    function renderKpiDashboard(kpiDashboard) {
        const payload = kpiDashboard && typeof kpiDashboard === 'object' ? kpiDashboard : {};

        setText('kpiAvgResponseDays', formatNumber(payload.avg_response_time_days || 0, 2));
        setText('kpiClosedRiskRate', formatNumber(payload.closed_risk_rate || 0, 2) + '%');
        setText('kpiResponseWithinSlaPercent', formatNumber(payload.response_within_sla_percent || 0, 2) + '%');
        setText('kpiOverdueTreatmentRate', formatNumber(payload.overdue_treatment_rate || 0, 2) + '%');
        setText('kpiResponseSlaDays', formatNumber(payload.response_sla_days || 7, 0));
        setText('kpiResponseSampleCount', formatNumber(payload.response_sample_count || 0, 0));
    }

    function renderDepartmentPerformance(departmentPerformance) {
        const payload = departmentPerformance && typeof departmentPerformance === 'object' ? departmentPerformance : {};
        const rows = Array.isArray(payload.rows) ? payload.rows : [];
        const board = document.getElementById('departmentCardsBoard');
        const totalRiskCount = Number(payload.total_risks || 0);
        const divisionCount = Number(payload.division_count || rows.length || 0);

        setText('departmentTopDivision', payload.top_division || '-');
        setText('departmentAvgScore', formatNumber(payload.average_score || 0, 2));
        setText('departmentTotalRiskCount', formatNumber(totalRiskCount, 0));
        setText('departmentDivisionCount', formatNumber(divisionCount, 0));

        if (!board) {
            return;
        }

        if (rows.length === 0) {
            board.innerHTML = '<div class="text-center text-muted py-3">No department performance data</div>';
            return;
        }

        function priorityCount(priority, key) {
            const value = Number(priority && typeof priority === 'object' ? (priority[key] || 0) : 0);
            return Number.isFinite(value) ? value : 0;
        }

        board.innerHTML = rows.map(function (row) {
            const priority = row && typeof row === 'object' && row.priority_breakdown && typeof row.priority_breakdown === 'object'
                ? row.priority_breakdown
                : {};
            const inherent = priority && typeof priority.inherent === 'object' ? priority.inherent : {};
            const residual = priority && typeof priority.residual === 'object' ? priority.residual : {};
            const treatment = row && typeof row === 'object' && row.treatment_stats && typeof row.treatment_stats === 'object'
                ? row.treatment_stats
                : {};

            return [
                '<div class="division-risk-card">',
                '<div class="division-risk-card-head">',
                '<div>',
                '<div class="division-risk-count">', formatNumber(row && row.risk_count ? row.risk_count : 0, 0), ' <span>Risks</span></div>',
                '<div class="division-risk-name">', escapeHtml(row && row.division ? row.division : '-'), '</div>',
                '</div>',
                '<div class="division-risk-rank">#', formatNumber(row && row.rank ? row.rank : 0, 0), '</div>',
                '</div>',

                '<table class="table table-sm mb-2 division-priority-table">',
                '<thead><tr><th>Priority</th><th class="text-end">Inherent</th><th class="text-end">Residual</th></tr></thead>',
                '<tbody>',
                '<tr><td><span class="risk-priority-dot low"></span> Low</td><td class="text-end">', formatNumber(priorityCount(inherent, 'low'), 0), '</td><td class="text-end">', formatNumber(priorityCount(residual, 'low'), 0), '</td></tr>',
                '<tr><td><span class="risk-priority-dot moderate"></span> Moderate</td><td class="text-end">', formatNumber(priorityCount(inherent, 'moderate'), 0), '</td><td class="text-end">', formatNumber(priorityCount(residual, 'moderate'), 0), '</td></tr>',
                '<tr><td><span class="risk-priority-dot high"></span> High</td><td class="text-end">', formatNumber(priorityCount(inherent, 'high'), 0), '</td><td class="text-end">', formatNumber(priorityCount(residual, 'high'), 0), '</td></tr>',
                '<tr><td><span class="risk-priority-dot extreme"></span> Extreme</td><td class="text-end">', formatNumber(priorityCount(inherent, 'extreme'), 0), '</td><td class="text-end">', formatNumber(priorityCount(residual, 'extreme'), 0), '</td></tr>',
                '</tbody>',
                '</table>',

                '<div class="division-treatment-stats">',
                '<div class="division-treatment-item"><span class="label">Activities</span><span class="value">', formatNumber(treatment.activity_count || 0, 0), '</span></div>',
                '<div class="division-treatment-item"><span class="label">Effectiveness</span><span class="value">', formatNumber(treatment.effectiveness_percent || 0, 2), '%</span></div>',
                '<div class="division-treatment-item"><span class="label">Overdue</span><span class="value">', formatNumber(treatment.overdue_count || 0, 0), ' / ', formatNumber(treatment.due_count || 0, 0), '</span></div>',
                '<div class="division-treatment-item"><span class="label">Closed Rate</span><span class="value">', formatNumber(row && row.closed_rate ? row.closed_rate : 0, 2), '%</span></div>',
                '<div class="division-treatment-item"><span class="label">Resp (Days)</span><span class="value">', formatNumber(row && row.avg_response_time_days ? row.avg_response_time_days : 0, 2), '</span></div>',
                '<div class="division-treatment-item score"><span class="label">Score</span><span class="value">', formatNumber(row && row.performance_score ? row.performance_score : 0, 2), '</span></div>',
                '</div>',
                '</div>'
            ].join('');
        }).join('');
    }

    function renderRootCauseAnalysis(rootCauseAnalysis) {
        const payload = rootCauseAnalysis && typeof rootCauseAnalysis === 'object' ? rootCauseAnalysis : {};
        const categoryRows = Array.isArray(payload.top_categories) ? payload.top_categories : [];
        const keywordRows = Array.isArray(payload.top_keywords) ? payload.top_keywords : [];
        const patternRows = Array.isArray(payload.patterns) ? payload.patterns : [];

        const categoryBody = document.getElementById('rootCauseCategoryBody');
        if (categoryBody) {
            if (categoryRows.length === 0) {
                categoryBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No cause category data</td></tr>';
            } else {
                categoryBody.innerHTML = categoryRows.map(function (row) {
                    return [
                        '<tr>',
                        '<td>', escapeHtml(row && row.category ? row.category : '-'), '</td>',
                        '<td class="text-end">', formatNumber(row && row.count ? row.count : 0, 0), '</td>',
                        '<td class="text-end">', formatNumber(row && row.share_percent ? row.share_percent : 0, 2), '%</td>',
                        '<td class="text-end">', formatNumber(row && row.avg_residual_score ? row.avg_residual_score : 0, 2), '</td>',
                        '</tr>'
                    ].join('');
                }).join('');
            }
        }

        const keywordBody = document.getElementById('rootCauseKeywordBody');
        if (keywordBody) {
            if (keywordRows.length === 0) {
                keywordBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No recurring keyword</td></tr>';
            } else {
                keywordBody.innerHTML = keywordRows.map(function (row) {
                    return [
                        '<tr>',
                        '<td>', escapeHtml(row && row.keyword ? row.keyword : '-'), '</td>',
                        '<td class="text-end">', formatNumber(row && row.count ? row.count : 0, 0), '</td>',
                        '</tr>'
                    ].join('');
                }).join('');
            }
        }

        const patternBody = document.getElementById('rootCausePatternBody');
        if (patternBody) {
            if (patternRows.length === 0) {
                patternBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No pattern insights available</td></tr>';
            } else {
                patternBody.innerHTML = patternRows.map(function (row) {
                    return [
                        '<tr>',
                        '<td>', escapeHtml(row && row.pattern ? row.pattern : '-'), '</td>',
                        '<td>', escapeHtml(row && row.evidence ? row.evidence : '-'), '</td>',
                        '<td>', escapeHtml(row && row.recommendation ? row.recommendation : '-'), '</td>',
                        '</tr>'
                    ].join('');
                }).join('');
            }
        }
    }

    function renderStatusPie(statusRows) {
        const pie = document.getElementById('statusPieChart');
        const center = document.getElementById('statusPieCenter');
        const legend = document.getElementById('statusPieLegend');
        if (!pie || !center || !legend) {
            return;
        }

        const rows = normalizeCountRows(statusRows);
        if (rows.length === 0) {
            pie.style.background = '#e2e8f0';
            center.innerHTML = '<div class="status-pie-total">0</div><div class="status-pie-label">Risks</div>';
            legend.innerHTML = '<div class="text-muted small text-center">No status data</div>';
            return;
        }

        const total = rows.reduce(function (sum, row) {
            return sum + row.count;
        }, 0);

        let currentPercent = 0;
        const gradientSegments = rows.map(function (row, index) {
            const color = colorByLabel(row.label, index);
            const percentage = (row.count / total) * 100;
            const start = currentPercent;
            currentPercent += percentage;
            row.__percentage = percentage;
            row.__color = color;
            return color + ' ' + start.toFixed(2) + '% ' + currentPercent.toFixed(2) + '%';
        });

        pie.style.background = 'conic-gradient(' + gradientSegments.join(', ') + ')';
        center.innerHTML = '<div class="status-pie-total">' + formatNumber(total, 0) + '</div><div class="status-pie-label">Risks</div>';

        legend.innerHTML = rows.map(function (row) {
            return [
                '<div class="status-legend-item">',
                '<span class="status-legend-left"><span class="status-legend-dot" style="background:', row.__color, '"></span>', escapeHtml(row.label), '</span>',
                '<span class="status-legend-value">', formatNumber(row.count, 0), ' (', formatNumber(row.__percentage, 1), '%)</span>',
                '</div>'
            ].join('');
        }).join('');
    }

    function renderCategoryBars(categoryRows) {
        const container = document.getElementById('categoryBarChart');
        if (!container) {
            return;
        }

        const rows = normalizeCountRows(categoryRows).slice(0, 5);
        if (rows.length === 0) {
            container.innerHTML = '<div class="text-muted small text-center py-2">No category data</div>';
            return;
        }

        const maxCount = rows.reduce(function (max, row) {
            return row.count > max ? row.count : max;
        }, 0) || 1;

        container.innerHTML = rows.map(function (row, index) {
            const width = clampPercent((row.count / maxCount) * 100);
            const color = defaultChartColors[index % defaultChartColors.length];

            return [
                '<div class="category-bar-row">',
                '<div class="category-bar-head">',
                '<span class="category-bar-label">', escapeHtml(row.label), '</span>',
                '<span class="category-bar-value">', formatNumber(row.count, 0), '</span>',
                '</div>',
                '<div class="category-bar-track"><div class="category-bar-fill" style="width:', width.toFixed(2), '%;background:', color, '"></div></div>',
                '</div>'
            ].join('');
        }).join('');
    }

    function compactCountRows(rows, maxItems = 5) {
        const safeRows = normalizeCountRows(rows);
        if (safeRows.length <= maxItems) {
            return safeRows;
        }

        const keepCount = Math.max(1, maxItems - 1);
        const keptRows = safeRows.slice(0, keepCount);
        const othersCount = safeRows.slice(keepCount).reduce(function (sum, row) {
            return sum + row.count;
        }, 0);
        if (othersCount > 0) {
            keptRows.push({
                label: 'Others',
                count: othersCount,
            });
        }

        return keptRows;
    }

    function resolvePaletteColor(label, index, colorMap) {
        const normalizedLabel = String(label || '').toLowerCase().trim();
        if (colorMap && typeof colorMap === 'object' && colorMap[normalizedLabel]) {
            return colorMap[normalizedLabel];
        }

        const statusMapKey = Object.keys(statusColorMap).find(function (key) {
            return key.toLowerCase() === normalizedLabel;
        });
        if (statusMapKey) {
            return statusColorMap[statusMapKey];
        }

        return defaultChartColors[index % defaultChartColors.length];
    }

    function renderPortfolioDonut(config) {
        const donut = document.getElementById(config.donutId);
        const center = document.getElementById(config.centerId);
        const legend = document.getElementById(config.legendId);
        if (!donut || !center || !legend) {
            return;
        }

        const rows = compactCountRows(config.rows, config.maxItems || 5);
        if (rows.length === 0) {
            donut.style.background = '#e2e8f0';
            center.textContent = '0';
            legend.innerHTML = '<div class="text-muted small text-center">No data</div>';
            return;
        }

        const total = rows.reduce(function (sum, row) {
            return sum + row.count;
        }, 0);

        let currentPercent = 0;
        const gradientSegments = rows.map(function (row, index) {
            const color = resolvePaletteColor(row.label, index, config.colorMap || {});
            const percentage = total > 0 ? (row.count / total) * 100 : 0;
            const start = currentPercent;
            currentPercent += percentage;
            row.__color = color;
            row.__percentage = percentage;

            return color + ' ' + start.toFixed(2) + '% ' + currentPercent.toFixed(2) + '%';
        });

        donut.style.background = 'conic-gradient(' + gradientSegments.join(', ') + ')';
        center.textContent = formatNumber(total, 0);
        legend.innerHTML = rows.map(function (row) {
            const labelEsc = escapeHtml(row.label);
            const countFormatted = formatNumber(row.count, 0);
            return [
                '<div class="portfolio-mini-legend-item" title="', labelEsc, ': ', countFormatted, '">',
                '<div class="portfolio-mini-legend-left">',
                '<span class="portfolio-mini-legend-dot" style="background:', row.__color, '"></span>',
                '<span class="portfolio-mini-legend-text">', labelEsc, '</span>',
                '</div>',
                '<span class="portfolio-mini-legend-value">', countFormatted, '</span>',
                '</div>'
            ].join('');
        }).join('');
    }

    function resolvePriorityClass(bucket) {
        const normalized = String(bucket || '').toLowerCase().trim();
        if (['low', 'moderate', 'high', 'extreme'].indexOf(normalized) >= 0) {
            return normalized;
        }

        return 'low';
    }

    function renderPortfolioTopRisks(rows) {
        const body = document.getElementById('portfolioTopRiskBody');
        if (!body) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="11" class="text-center text-muted">No top risk data available.</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (row) {
            const riskValue = Number(row && row.risk_value ? row.risk_value : 0);
            const likelihoodValue = Number(row && row.likelihood ? row.likelihood : Number.NaN);
            const impactValue = Number(row && row.impact ? row.impact : Number.NaN);
            const priorityClass = resolvePriorityClass(row && row.priority_bucket ? row.priority_bucket : '');

            return [
                '<tr>',
                '<td>', escapeHtml(row && row.risk_id ? row.risk_id : '-'), '</td>',
                '<td>', escapeHtml(row && row.risk_title ? row.risk_title : '-'), '</td>',
                '<td>', escapeHtml(row && row.description ? row.description : '-'), '</td>',
                '<td class="text-center"><span class="portfolio-priority-pill priority-', priorityClass, '">', escapeHtml(row && row.priority_label ? row.priority_label : '-'), '</span></td>',
                '<td class="text-end">', formatNumber(riskValue, 2), '</td>',
                '<td>', escapeHtml(row && row.division ? row.division : '-'), '</td>',
                '<td>', escapeHtml(row && row.owner ? row.owner : '-'), '</td>',
                '<td>', escapeHtml(row && row.category ? row.category : '-'), '</td>',
                '<td class="text-end">', Number.isFinite(likelihoodValue) ? formatNumber(likelihoodValue, 0) : '-', '</td>',
                '<td class="text-end">', Number.isFinite(impactValue) ? formatNumber(impactValue, 0) : '-', '</td>',
                '<td>', escapeHtml(row && row.control_focus ? row.control_focus : '-'), '</td>',
                '</tr>'
            ].join('');
        }).join('');
    }

    function renderPortfolioOverview(portfolioOverview) {
        const payload = portfolioOverview && typeof portfolioOverview === 'object' ? portfolioOverview : {};

        renderPortfolioDonut({
            donutId: 'portfolioPriorityDonut',
            centerId: 'portfolioPriorityTotal',
            legendId: 'portfolioPriorityLegend',
            rows: payload.by_priority || [],
            maxItems: 4,
            colorMap: {
                low: '#4caf50',
                moderate: '#facc15',
                high: '#fb8c00',
                extreme: '#ef4444',
            },
        });

        renderPortfolioDonut({
            donutId: 'portfolioCategoryDonut',
            centerId: 'portfolioCategoryTotal',
            legendId: 'portfolioCategoryLegend',
            rows: payload.by_category || [],
            maxItems: 5,
            colorMap: {},
        });

        renderPortfolioDonut({
            donutId: 'portfolioDivisionDonut',
            centerId: 'portfolioDivisionTotal',
            legendId: 'portfolioDivisionLegend',
            rows: payload.by_division || [],
            maxItems: 5,
            colorMap: {},
        });

        renderPortfolioDonut({
            donutId: 'portfolioTreatmentDonut',
            centerId: 'portfolioTreatmentTotal',
            legendId: 'portfolioTreatmentLegend',
            rows: payload.treatment_timeline || [],
            maxItems: 4,
            colorMap: {
                overdue: '#dc2626',
                'due soon': '#f59e0b',
                'on track': '#16a34a',
                'no deadline': '#64748b',
            },
        });

        renderPortfolioDonut({
            donutId: 'portfolioStatusDonut',
            centerId: 'portfolioStatusTotal',
            legendId: 'portfolioStatusLegend',
            rows: payload.risk_status || [],
            maxItems: 4,
            colorMap: {},
        });

        renderPortfolioTopRisks(payload.top_risks || []);
    }

    function renderDashboardSummary(summary) {
        if (!summary || typeof summary !== 'object') {
            return;
        }
        latestDashboardSummary = summary;

        setText('dashboardGeneratedAt', summary.generated_at || '-');
        setText('metricTotalRisks', formatNumber(summary.total_risks || 0, 0));
        setText('metricOpenRisks', formatNumber(summary.open_count || 0, 0));
        setText('metricMonitoringRisks', formatNumber(summary.monitoring_count || 0, 0));
        setText('metricClosedRisks', formatNumber(summary.closed_count || 0, 0));

        const averageScores = summary.average_scores || {};
        setText('metricAvgInherent', formatNumber(averageScores.inherent || 0, 2));
        setText('metricAvgResidual', formatNumber(averageScores.residual || 0, 2));
        setText('metricAvgOverall', formatNumber(averageScores.overall || 0, 2));

        const variance = summary.variance || {};
        setText('metricGlobalVariance', formatNumber(variance.global || 0, 2));

        const mitigation = summary.mitigation || {};
        const mitigationPercent = Number(mitigation.effectiveness_percent || 0);
        setText('mitigationEffectivenessText', formatNumber(mitigationPercent, 2) + '%');
        setText('mitigationTreatmentCount', formatNumber(mitigation.treatment_count || 0, 0));
        setText('mitigationCoveragePercent', formatNumber(mitigation.risk_coverage_percent || 0, 2) + '%');
        setProgressBar('mitigationEffectivenessBar', mitigationPercent);

        const sla = summary.sla || {};
        const slaActual = Number(sla.actual_percent || 0);
        const slaTarget = Number(sla.target_percent || 95);
        setText('slaActualPercent', formatNumber(slaActual, 2) + '%');
        setText('slaTargetPercent', formatNumber(slaTarget, 2) + '%');
        setText('slaDueCount', formatNumber(sla.due_count || 0, 0));
        setText('slaCompliantCount', formatNumber(sla.compliant_count || 0, 0));
        setText('slaOverdueCount', formatNumber(sla.overdue_count || 0, 0));
        setProgressBar('slaComplianceBar', slaActual);

        const slaBar = document.getElementById('slaComplianceBar');
        if (slaBar) {
            slaBar.classList.remove('bg-success', 'bg-danger');
            slaBar.classList.add(slaActual >= slaTarget ? 'bg-success' : 'bg-danger');
        }
        renderBenchmarkScoring(summary.benchmark_scoring || {});
        renderKpiDashboard(summary.kpi_dashboard || {});
        renderDepartmentPerformance(summary.department_performance || {});
        renderRootCauseAnalysis(summary.root_cause_analysis || {});
        renderPortfolioOverview(summary.portfolio_overview || {});

        renderVarianceRows((summary.variance && summary.variance.rows) || []);
    }

    function readJsonStorage(key, fallbackValue) {
        try {
            const raw = localStorage.getItem(key);
            if (!raw) {
                return fallbackValue;
            }
            const parsed = JSON.parse(raw);
            return parsed || fallbackValue;
        } catch (error) {
            return fallbackValue;
        }
    }

    function writeJsonStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            // Ignore blocked storage.
        }
    }

    function applyDashboardTabFilter(tabId) {
        const safeTab = normalizeDashboardTab(tabId);
        activeDashboardTab = safeTab;

        if (widgetGrid) {
            widgetGrid.querySelectorAll('.dashboard-widget').forEach(function (widget) {
                const widgetTab = normalizeDashboardTab(widget.dataset.widgetTab || defaultDashboardTab);
                widget.classList.toggle('dashboard-widget-tab-hidden', widgetTab !== safeTab);
            });
        }

        document.querySelectorAll('[data-dashboard-tab]').forEach(function (button) {
            const isActive = normalizeDashboardTab(button.dataset.dashboardTab) === safeTab;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        writeJsonStorage(dashboardTabKey, safeTab);
    }

    function setupDashboardTabs() {
        const buttons = Array.from(document.querySelectorAll('[data-dashboard-tab]'));
        if (buttons.length === 0) {
            return;
        }

        const preferredTab = normalizeDashboardTab(readJsonStorage(dashboardTabKey, defaultDashboardTab));
        applyDashboardTabFilter(preferredTab);

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                applyDashboardTabFilter(button.dataset.dashboardTab || defaultDashboardTab);
            });
        });
    }

    async function loadServerPreference() {
        if (!preferenceGetUrl) {
            return null;
        }

        try {
            const response = await fetch(preferenceGetUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                return null;
            }

            return await response.json();
        } catch (error) {
            return null;
        }
    }

    function applyServerPreference(preference) {
        if (!preference || typeof preference !== 'object') {
            return;
        }

        if (preference.widget_visibility && typeof preference.widget_visibility === 'object') {
            writeJsonStorage(visibilityKey, preference.widget_visibility);
        }

        if (Array.isArray(preference.widget_order)) {
            writeJsonStorage(orderKey, preference.widget_order);
        }
    }

    function queuePreferenceSync() {
        if (!preferenceSaveUrl) {
            return;
        }

        if (preferenceSyncTimer) {
            window.clearTimeout(preferenceSyncTimer);
        }

        preferenceSyncTimer = window.setTimeout(function () {
            syncPreferenceToServer();
        }, 450);
    }

    async function syncPreferenceToServer() {
        if (!preferenceSaveUrl) {
            return;
        }

        const widgetVisibility = readJsonStorage(visibilityKey, {});
        const widgetOrder = readJsonStorage(orderKey, []);

        try {
            await fetch(preferenceSaveUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    widget_visibility: widgetVisibility,
                    widget_order: widgetOrder,
                }),
            });
        } catch (error) {
            // Keep local preference when sync fails.
        }
    }

    function applyWidgetOrder() {
        if (!widgetGrid) {
            return;
        }

        const storedOrder = readJsonStorage(orderKey, []);
        if (!Array.isArray(storedOrder) || storedOrder.length === 0) {
            return;
        }

        const widgets = Array.from(widgetGrid.querySelectorAll('.dashboard-widget'));
        const widgetMap = {};
        widgets.forEach(function (widget) {
            widgetMap[widget.dataset.widgetId] = widget;
        });

        storedOrder.forEach(function (widgetId) {
            if (widgetMap[widgetId]) {
                widgetGrid.appendChild(widgetMap[widgetId]);
            }
        });
    }

    function persistWidgetOrder() {
        if (!widgetGrid) {
            return;
        }

        const order = Array.from(widgetGrid.querySelectorAll('.dashboard-widget'))
            .map(function (widget) {
                return widget.dataset.widgetId;
            })
            .filter(Boolean);
        writeJsonStorage(orderKey, order);
        queuePreferenceSync();
    }

    function setupWidgetCustomizeDropdown() {
        const toggle = document.querySelector('[data-widget-customize-toggle]');
        const menu = document.querySelector('[data-widget-customize-menu]');
        if (!toggle || !menu) {
            return;
        }

        const container = toggle.closest('.dropdown') || dashboardRoot;

        function isOpen() {
            return menu.classList.contains('show');
        }

        function openMenu() {
            menu.classList.add('show');
            if (container) {
                container.classList.add('show');
            }
            toggle.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            menu.classList.remove('show');
            if (container) {
                container.classList.remove('show');
            }
            toggle.setAttribute('aria-expanded', 'false');
        }

        function toggleMenu() {
            if (isOpen()) {
                closeMenu();
            } else {
                openMenu();
            }
        }

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleMenu();
        });

        toggle.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                event.stopPropagation();
                toggleMenu();
            }

            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        menu.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        document.addEventListener('click', function (event) {
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });
    }

    function applyWidgetVisibilityState(state) {
        if (!widgetGrid) {
            return;
        }

        const safeState = state && typeof state === 'object' ? state : {};
        widgetGrid.querySelectorAll('.dashboard-widget').forEach(function (widget) {
            const widgetId = widget.dataset.widgetId;
            const isVisible = safeState[widgetId] !== false;
            widget.classList.toggle('dashboard-widget-hidden', !isVisible);
        });

        document.querySelectorAll('.widget-visibility-toggle').forEach(function (checkbox) {
            const widgetId = checkbox.dataset.widgetId;
            checkbox.checked = safeState[widgetId] !== false;
        });

        applyDashboardTabFilter(activeDashboardTab);
    }

    function setupWidgetVisibility() {
        const currentState = readJsonStorage(visibilityKey, {});
        applyWidgetVisibilityState(currentState);

        document.querySelectorAll('.widget-visibility-toggle').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const latestState = readJsonStorage(visibilityKey, {});
                latestState[checkbox.dataset.widgetId] = checkbox.checked;
                writeJsonStorage(visibilityKey, latestState);
                applyWidgetVisibilityState(latestState);
                queuePreferenceSync();
            });
        });
    }

    function setupWidgetDragDrop() {
        if (!widgetGrid) {
            return;
        }

        let activeWidget = null;
        const widgets = Array.from(widgetGrid.querySelectorAll('.dashboard-widget'));

        widgets.forEach(function (widget) {
            widget.addEventListener('dragstart', function (event) {
                activeWidget = widget;
                widget.classList.add('dragging');
                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', widget.dataset.widgetId || '');
                }
            });

            widget.addEventListener('dragend', function () {
                widget.classList.remove('dragging');
                activeWidget = null;
                persistWidgetOrder();
            });
        });

        widgetGrid.addEventListener('dragover', function (event) {
            if (!activeWidget) {
                return;
            }
            event.preventDefault();

            const sibling = findDropTarget(widgetGrid, event.clientY);
            if (!sibling) {
                widgetGrid.appendChild(activeWidget);
                return;
            }

            if (sibling !== activeWidget) {
                widgetGrid.insertBefore(activeWidget, sibling);
            }
        });
    }

    function findDropTarget(container, mouseY) {
        const candidates = Array.from(container.querySelectorAll('.dashboard-widget:not(.dragging):not(.dashboard-widget-hidden):not(.dashboard-widget-tab-hidden)'));
        let closest = {
            offset: Number.NEGATIVE_INFINITY,
            element: null,
        };

        candidates.forEach(function (candidate) {
            const rect = candidate.getBoundingClientRect();
            const offset = mouseY - rect.top - (rect.height / 2);
            if (offset < 0 && offset > closest.offset) {
                closest = {
                    offset: offset,
                    element: candidate,
                };
            }
        });

        return closest.element;
    }

    function parseInitialSummary() {
        const summaryNode = document.getElementById('riskDashboardInitialData');
        if (!summaryNode) {
            return null;
        }

        try {
            return JSON.parse(summaryNode.textContent || '{}');
        } catch (error) {
            return null;
        }
    }

    let fetchInFlight = false;
    async function refreshDashboardSummary() {
        if (!summaryUrl || fetchInFlight) {
            return;
        }

        fetchInFlight = true;
        try {
            const query = new URLSearchParams(window.location.search);
            const endpoint = query.toString() ? (summaryUrl + '?' + query.toString()) : summaryUrl;
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load dashboard summary.');
            }

            const payload = await response.json();
            renderDashboardSummary(payload);
        } catch (error) {
            // Keep previous values if refresh fails.
        } finally {
            fetchInFlight = false;
        }
    }

    async function initializeDashboard() {
        const serverPreference = await loadServerPreference();
        applyServerPreference(serverPreference);

        applyWidgetOrder();
        setupWidgetCustomizeDropdown();
        setupWidgetVisibility();
        setupDashboardTabs();
        setupWidgetDragDrop();

        const initialSummary = parseInitialSummary();
        if (initialSummary) {
            renderDashboardSummary(initialSummary);
        } else {
            refreshDashboardSummary();
        }

        const intervalMs = Number.isFinite(refreshMs) && refreshMs >= 10000 ? refreshMs : 30000;
        window.setInterval(refreshDashboardSummary, intervalMs);
    }

    initializeDashboard();
})();
</script>
