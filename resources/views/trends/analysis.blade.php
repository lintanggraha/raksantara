@extends('layouts.tabler')

@section('title', 'Risk Trend Analysis')

@section('css')
<style>
.trend-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.6rem;
    background: #fff;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}

.trend-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.trend-controls {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 0.6rem;
    padding: 1rem;
    text-align: center;
}

.stat-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
}

.stat-value.positive {
    color: #059669;
}

.stat-value.neutral {
    color: #0ea5e9;
}

.chart-container {
    position: relative;
    height: 350px;
    margin-bottom: 2rem;
}

.chart-small {
    height: 250px;
}

.trend-panels {
    margin-bottom: 1.5rem;
}

.trend-draggable {
    position: relative;
}

.trend-draggable.dragging {
    opacity: 0.55;
    border-style: dashed;
    border-color: #0ea5e9;
}

.trend-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.trend-drag-handle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.9rem;
    height: 1.9rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.45rem;
    background: #f8fafc;
    color: #64748b;
    cursor: grab;
    flex-shrink: 0;
}

.trend-drag-handle:active {
    cursor: grabbing;
}

@media (max-width: 768px) {
    .trend-header {
        flex-direction: column;
    }
    
    .stat-grid {
        grid-template-columns: 1fr;
    }

    .trend-panel-header {
        align-items: center;
    }
}
</style>
@endsection

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0">Risk Trend Analysis</h1>
        <p class="text-muted mb-0">Monitor risk metrics over time by {{ ucfirst($granularity) }}</p>
    </div>
</div>
@endsection

@section('content')

<div class="trend-card">
    <div class="trend-header">
        <div>
            <h5 class="mb-0">Period & Granularity</h5>
        </div>
        <div class="trend-controls">
            <form action="{{ route('trends.analysis') }}" method="GET" class="d-flex gap-2 align-items-center">
                <select name="granularity" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="monthly" @if($granularity === 'monthly') selected @endif>Monthly</option>
                    <option value="quarterly" @if($granularity === 'quarterly') selected @endif>Quarterly</option>
                    <option value="yearly" @if($granularity === 'yearly') selected @endif>Yearly</option>
                </select>

                <select name="days" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="90" @if($daysBack == 90) selected @endif>Last 3 Months</option>
                    <option value="180" @if($daysBack == 180) selected @endif>Last 6 Months</option>
                    <option value="365" @if($daysBack == 365) selected @endif>Last 1 Year</option>
                    <option value="730" @if($daysBack == 730) selected @endif>Last 2 Years</option>
                </select>
            </form>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Avg Inherent Risk Score</div>
        <div class="stat-value neutral">{{ $statistics['avg_inherent'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Avg Residual Risk Score</div>
        <div class="stat-value neutral">{{ $statistics['avg_residual'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Risk Reduction</div>
        <div class="stat-value positive">{{ $statistics['improvement_percentage'] }}%</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Risks</div>
        <div class="stat-value">{{ $statistics['total_risks'] }}</div>
    </div>
</div>

<div id="trendPanelContainer" class="trend-panels">
    <!-- Main Trend Charts -->
    <div class="trend-card trend-draggable" draggable="true" data-trend-panel-id="risk_score_trend">
        <div class="trend-panel-header">
            <h5 class="mb-0">Risk Score Trend (Inherent vs Residual)</h5>
            <span class="trend-drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
        </div>
        <div class="chart-container">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Category Comparison -->
    <div class="trend-card trend-draggable" draggable="true" data-trend-panel-id="category_comparison">
        <div class="trend-panel-header">
            <h5 class="mb-0">Risk Score by Category</h5>
            <span class="trend-drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
        </div>
        <div class="chart-container chart-small">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Status Distribution Over Time -->
    <div class="trend-card trend-draggable" draggable="true" data-trend-panel-id="status_distribution">
        <div class="trend-panel-header">
            <h5 class="mb-0">Risk Status Distribution Over Time</h5>
            <span class="trend-drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
        </div>
        <div class="chart-container">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Owner Concentration -->
    <div class="trend-card trend-draggable" draggable="true" data-trend-panel-id="owner_concentration">
        <div class="trend-panel-header">
            <h5 class="mb-0">Risk Concentration by Owner (Top 10)</h5>
            <span class="trend-drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
        </div>
        <div class="chart-container">
            <canvas id="ownerChart"></canvas>
        </div>
    </div>

    <!-- Heatmap High-Risk Trend -->
    <div class="trend-card trend-draggable" draggable="true" data-trend-panel-id="heatmap_high_risk">
        <div class="trend-panel-header">
            <div>
                <h5 class="mb-1">Heatmap High-Risk Trend</h5>
                <div class="small text-muted">
                    Latest: {{ $heatmapTrends['latest_period'] ?? '-' }} |
                    Change Residual High: {{ $heatmapTrends['change_residual_high_count'] ?? 0 }} |
                    Direction: <strong>{{ strtoupper((string) ($heatmapTrends['direction'] ?? 'stable')) }}</strong>
                </div>
            </div>
            <span class="trend-drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
        </div>
        <div class="chart-container">
            <canvas id="heatmapTrendChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
(function() {
    const trendData = @json($trendData);
    const categories = @json($categories);
    const statusTrends = @json($statusTrends);
    const ownerTrends = @json($ownerTrends);
    const heatmapTrends = @json($heatmapTrends);
    const trendPanelContainer = document.getElementById('trendPanelContainer');
    const trendPanelOrderKey = 'risk_trend_panel_order_v1';

    function readPanelOrder() {
        try {
            const raw = window.localStorage.getItem(trendPanelOrderKey);
            if (!raw) {
                return [];
            }

            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function writePanelOrder(order) {
        try {
            window.localStorage.setItem(trendPanelOrderKey, JSON.stringify(order));
        } catch (error) {
            // Ignore localStorage exceptions.
        }
    }

    function applyPanelOrder(storedOrder) {
        if (!trendPanelContainer) {
            return;
        }

        const panels = Array.from(trendPanelContainer.querySelectorAll('[data-trend-panel-id]'));
        if (panels.length === 0) {
            return;
        }

        const panelMap = {};
        panels.forEach(function (panel) {
            panelMap[panel.dataset.trendPanelId] = panel;
        });

        const orderedPanels = [];
        storedOrder.forEach(function (panelId) {
            if (panelMap[panelId]) {
                orderedPanels.push(panelMap[panelId]);
            }
        });

        panels.forEach(function (panel) {
            if (!orderedPanels.includes(panel)) {
                orderedPanels.push(panel);
            }
        });

        orderedPanels.forEach(function (panel) {
            trendPanelContainer.appendChild(panel);
        });
    }

    function persistPanelOrder() {
        if (!trendPanelContainer) {
            return;
        }

        const order = Array.from(trendPanelContainer.querySelectorAll('[data-trend-panel-id]'))
            .map(function (panel) {
                return panel.dataset.trendPanelId;
            })
            .filter(function (panelId) {
                return panelId && panelId.length > 0;
            });

        writePanelOrder(order);
    }

    function findPanelDropTarget(container, mouseY) {
        const candidates = Array.from(container.querySelectorAll('.trend-draggable:not(.dragging)'));
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

    function setupPanelDragDrop() {
        if (!trendPanelContainer) {
            return;
        }

        let activePanel = null;
        const panels = Array.from(trendPanelContainer.querySelectorAll('.trend-draggable'));
        if (panels.length === 0) {
            return;
        }

        panels.forEach(function (panel) {
            panel.addEventListener('dragstart', function (event) {
                activePanel = panel;
                panel.classList.add('dragging');
                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', panel.dataset.trendPanelId || '');
                }
            });

            panel.addEventListener('dragend', function () {
                panel.classList.remove('dragging');
                activePanel = null;
                persistPanelOrder();
            });
        });

        trendPanelContainer.addEventListener('dragover', function (event) {
            if (!activePanel) {
                return;
            }
            event.preventDefault();

            const sibling = findPanelDropTarget(trendPanelContainer, event.clientY);
            if (!sibling) {
                trendPanelContainer.appendChild(activePanel);
                return;
            }

            if (sibling !== activePanel) {
                trendPanelContainer.insertBefore(activePanel, sibling);
            }
        });
    }

    applyPanelOrder(readPanelOrder());
    setupPanelDragDrop();

    if (!trendData || trendData.length === 0) {
        console.log('No trend data available');
        document.getElementById('trendChart').parentElement.innerHTML = '<div class="alert alert-info">No data available for the selected period</div>';
    }

    // Chart Colors
    const colors = {
        inherent: 'rgb(239, 68, 68)',
        residual: 'rgb(34, 197, 94)',
        open: 'rgb(239, 68, 68)',
        monitoring: 'rgb(251, 146, 60)',
        closed: 'rgb(34, 197, 94)',
        ownerCount: 'rgb(59, 130, 246)',
        ownerResidual: 'rgb(16, 185, 129)',
        highRiskReduction: 'rgb(14, 165, 233)',
    };

    // 1. Main Trend Chart (Line)
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    if (trendData && trendData.length > 0) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.period),
                datasets: [
                    {
                        label: 'Inherent Score',
                        data: trendData.map(d => d.inherent_avg),
                        borderColor: colors.inherent,
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: colors.inherent,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Residual Score',
                        data: trendData.map(d => d.residual_avg),
                        borderColor: colors.residual,
                        backgroundColor: 'rgba(34, 197, 94, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: colors.residual,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 12, weight: 600 },
                        }
                    },
                    filler: true,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 25,
                        ticks: {
                            font: { size: 11 },
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11 },
                        },
                        grid: {
                            display: false,
                        }
                    }
                },
            }
        });
    }

    // 2. Category Comparison (Bar)
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    if (categories && categories.length > 0) {
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: categories.map(c => c.category),
                datasets: [
                    {
                        label: 'Inherent Avg',
                        data: categories.map(c => c.inherent_avg),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: colors.inherent,
                        borderWidth: 1,
                    },
                    {
                        label: 'Residual Avg',
                        data: categories.map(c => c.residual_avg),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: colors.residual,
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 } }
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 25,
                        ticks: { font: { size: 10 } },
                    },
                    y: {
                        ticks: { font: { size: 11 } },
                    }
                },
            }
        });
    } else {
        document.getElementById('categoryChart').parentElement.innerHTML = '<div class="alert alert-info">No category data available.</div>';
    }

    // 3. Status Distribution Over Time (Stacked Bar)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const periodLabels = trendData.map(d => d.period);
    
    if (periodLabels.length > 0) {
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: periodLabels,
                datasets: [
                    {
                        label: 'Open',
                        data: (statusTrends.Open || []).map(s => s.count),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: colors.open,
                        borderWidth: 1,
                    },
                    {
                        label: 'Monitoring',
                        data: (statusTrends.Monitoring || []).map(s => s.count),
                        backgroundColor: 'rgba(251, 146, 60, 0.7)',
                        borderColor: colors.monitoring,
                        borderWidth: 1,
                    },
                    {
                        label: 'Closed',
                        data: (statusTrends.Closed || []).map(s => s.count),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: colors.closed,
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: undefined,
                scales: {
                    x: {
                        stacked: true,
                        ticks: { font: { size: 11 } },
                        grid: { display: false },
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { font: { size: 11 } },
                    },
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 }, padding: 15 }
                    },
                },
            }
        });
    } else {
        document.getElementById('statusChart').parentElement.innerHTML = '<div class="alert alert-info">No status trend data available.</div>';
    }

    // 4. Owner Concentration (Bar + Line)
    const ownerCtx = document.getElementById('ownerChart').getContext('2d');
    if (ownerTrends && ownerTrends.length > 0) {
        new Chart(ownerCtx, {
            type: 'bar',
            data: {
                labels: ownerTrends.map(o => o.owner),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Risk Count',
                        data: ownerTrends.map(o => o.count),
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: colors.ownerCount,
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Residual Avg',
                        data: ownerTrends.map(o => o.residual_avg),
                        borderColor: colors.ownerResidual,
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        borderWidth: 2,
                        tension: 0.35,
                        fill: false,
                        pointRadius: 4,
                        pointBackgroundColor: colors.ownerResidual,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 }, padding: 15 }
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Risk Count',
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        max: 25,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Residual Avg',
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 35,
                            minRotation: 0,
                        }
                    }
                },
            }
        });
    } else {
        document.getElementById('ownerChart').parentElement.innerHTML = '<div class="alert alert-info">No owner trend data available.</div>';
    }

    // 5. Heatmap High-Risk Trend (Mixed)
    const heatmapCtx = document.getElementById('heatmapTrendChart').getContext('2d');
    const heatmapRows = heatmapTrends && Array.isArray(heatmapTrends.rows) ? heatmapTrends.rows : [];
    if (heatmapRows.length > 0) {
        new Chart(heatmapCtx, {
            type: 'bar',
            data: {
                labels: heatmapRows.map(r => r.period),
                datasets: [
                    {
                        type: 'line',
                        label: 'Inherent High Count',
                        data: heatmapRows.map(r => r.inherent_high_count),
                        borderColor: colors.inherent,
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        tension: 0.35,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Residual High Count',
                        data: heatmapRows.map(r => r.residual_high_count),
                        borderColor: colors.residual,
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        tension: 0.35,
                        yAxisID: 'y',
                    },
                    {
                        type: 'bar',
                        label: 'High Risk Reduction',
                        data: heatmapRows.map(r => r.high_risk_reduction),
                        backgroundColor: 'rgba(14, 165, 233, 0.35)',
                        borderColor: colors.highRiskReduction,
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 }, padding: 15 }
                    },
                    tooltip: {
                        callbacks: {
                            afterBody: function(context) {
                                if (!context || context.length === 0) {
                                    return '';
                                }
                                const index = context[0].dataIndex;
                                const row = heatmapRows[index];
                                if (!row) {
                                    return '';
                                }
                                return [
                                    'Dominant Cell: ' + (row.dominant_residual_cell || '-'),
                                    'Reduction %: ' + (row.high_risk_reduction_percent || 0) + '%',
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Risk Count',
                        }
                    },
                    x: {
                        grid: { display: false },
                    }
                },
            }
        });
    } else {
        document.getElementById('heatmapTrendChart').parentElement.innerHTML = '<div class="alert alert-info">No heatmap trend data available.</div>';
    }
})();
</script>

@endsection
