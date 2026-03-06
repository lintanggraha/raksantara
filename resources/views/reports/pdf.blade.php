<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @php
        $pdfLayout = is_array($pdfLayout ?? null) ? $pdfLayout : [];
        $pdfMargins = is_array($pdfLayout['page_margin'] ?? null) ? $pdfLayout['page_margin'] : [];
        $pageMarginTop = (string) ($pdfMargins['top'] ?? '112px');
        $pageMarginRight = (string) ($pdfMargins['right'] ?? '18px');
        $pageMarginBottom = (string) ($pdfMargins['bottom'] ?? '74px');
        $pageMarginLeft = (string) ($pdfMargins['left'] ?? '18px');
        $pdfDensity = (string) ($pdfLayout['density'] ?? 'normal');
    @endphp
    <style>
        body {
            font-family: "Segoe UI", Arial, "DejaVu Sans", sans-serif;
            font-size: {{ $pdfDensity === 'ultra' ? '8.9px' : ($pdfDensity === 'compact' ? '9.4px' : '10px') }};
            color: #0f172a;
            margin: 0;
            background: #f5f8fc;
            line-height: 1.35;
        }

        h1 {
            margin: 0;
            font-size: 19px;
            color: #0f172a;
        }

        .doc-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            padding: 8px 14px;
            border-bottom: 1px solid #d7e1ee;
            background: #ffffff;
            box-sizing: border-box;
        }

        .doc-header .left {
            float: left;
            width: 60%;
        }

        .doc-header .right {
            float: right;
            width: 38%;
            text-align: right;
            color: #475569;
            font-size: 11px;
        }

        .doc-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 28px;
            padding: 6px 14px;
            border-top: 1px solid #d7e1ee;
            font-size: 10px;
            color: #475569;
            background: #f8fbff;
            box-sizing: border-box;
        }

        @page { margin: {{ $pageMarginTop }} {{ $pageMarginRight }} {{ $pageMarginBottom }} {{ $pageMarginLeft }}; }

        .page-number:before { content: counter(page); }

        .report-cover {
            margin-bottom: 14px;
            border: 1px solid #d8e3f1;
            background: #ffffff;
            padding: 10px 12px;
        }

        .cover-title {
            font-size: 16px;
            font-weight: 700;
            color: #123b66;
            margin: 0 0 8px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            font-size: 9px;
            background: #f8fbff;
        }

        .meta-key {
            width: 150px;
            font-weight: 700;
            color: #1e3a8a;
        }

        .section {
            margin-bottom: 14px;
            page-break-inside: auto;
            border: 1px solid #d8e3f1;
            background: #ffffff;
            padding: 8px;
        }

        .section + .section {
            margin-top: 12px;
        }

        .section h2 {
            margin: 0 0 4px;
            font-size: 13px;
            color: #0f2f4f;
            border-left: 4px solid #0f4c81;
            padding-left: 8px;
            page-break-after: avoid;
        }

        .section-subtitle {
            margin: 0 0 8px;
            font-size: 9px;
            color: #475569;
            page-break-after: avoid;
        }

        .section-keep-together {
            page-break-inside: auto;
        }

        .section h3 {
            margin: 10px 0 6px;
            font-size: 11px;
            color: #1e3a5f;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #d4deea;
            padding: 4px 5px;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
            hyphens: auto;
        }

        .data-table th {
            background: #0f4c81;
            color: #ffffff;
            font-size: 9px;
            text-align: left;
            font-weight: 700;
        }

        .data-table td {
            font-size: 8.8px;
            color: #1f2937;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tfoot {
            display: table-row-group;
        }

        .data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .data-table tbody tr:nth-child(even) td {
            background: #f8fbff;
        }

        .table-density-medium th,
        .table-density-medium td {
            padding: 3px 4px;
        }

        .table-density-medium th {
            font-size: 8.4px;
        }

        .table-density-medium td {
            font-size: 8px;
            line-height: 1.25;
        }

        .table-density-compact th,
        .table-density-compact td {
            padding: 2.5px 3.5px;
        }

        .table-density-compact th {
            font-size: 7.7px;
        }

        .table-density-compact td {
            font-size: 7.4px;
            line-height: 1.2;
        }

        .table-density-ultra th,
        .table-density-ultra td {
            padding: 2px 3px;
        }

        .table-density-ultra th {
            font-size: 7.1px;
        }

        .table-density-ultra td {
            font-size: 6.9px;
            line-height: 1.15;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 8px;
        }

        .kpi-cell {
            width: 25%;
            padding: 7px 8px;
            border: 1px solid #d7e1ee;
            background: #f8fbff;
        }

        .kpi-label {
            font-size: 8.2px;
            font-weight: 700;
            text-transform: uppercase;
            color: #334155;
            margin-bottom: 3px;
        }

        .kpi-value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }

        .kpi-tone-blue { background: #dbeafe; }
        .kpi-tone-red { background: #fee2e2; }
        .kpi-tone-amber { background: #fef3c7; }
        .kpi-tone-green { background: #dcfce7; }

        .board-insight {
            margin-top: 8px;
            border-left: 3px solid #0f4c81;
            background: #eef5ff;
            color: #1e3a5f;
            padding: 6px 8px;
            font-size: 8.8px;
            line-height: 1.35;
        }

        .board-chart-block {
            margin-bottom: 8px;
            border: 1px solid #d6e1ef;
            background: #f8fbff;
            padding: 8px;
        }

        .board-chart-title {
            font-size: 10px;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 6px;
        }

        .board-bar-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .board-bar-table td {
            border: 1px solid #d7e1ee;
            padding: 4px;
            font-size: 8.5px;
        }

        .board-bar-table th {
            border: 1px solid #d7e1ee;
            padding: 4px;
            background: #0f4c81;
            color: #ffffff;
            font-size: 8.4px;
            text-align: left;
            font-weight: 700;
        }

        .board-bar-table thead {
            display: table-header-group;
        }

        .board-bar-table tr {
            page-break-inside: avoid;
        }

        .board-bar-label {
            width: 130px;
            font-weight: 700;
            color: #334155;
        }

        .board-bar-track {
            width: 100%;
            background: #e2e8f0;
            height: 8px;
            position: relative;
        }

        .board-bar-fill {
            height: 8px;
        }

        .board-bar-value {
            width: 55px;
            text-align: right;
            color: #0f172a;
            font-weight: 700;
        }

        .board-tone-blue { background: #2563eb; }
        .board-tone-green { background: #16a34a; }
        .board-tone-amber { background: #f59e0b; }
        .board-tone-red { background: #dc2626; }

        .board-trend-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .board-trend-table td,
        .board-trend-table th {
            border: 1px solid #d7e1ee;
            padding: 4px;
            font-size: 8.5px;
            text-align: center;
        }

        .board-trend-table thead {
            display: table-header-group;
        }

        .board-trend-table tr {
            page-break-inside: avoid;
        }

        .board-priority-table th,
        .board-priority-table td {
            font-size: 7.9px;
            padding: 3px 4px;
        }

        .board-priority-table th:nth-child(1),
        .board-priority-table td:nth-child(1) { width: 8%; }
        .board-priority-table th:nth-child(2),
        .board-priority-table td:nth-child(2) { width: 23%; }
        .board-priority-table th:nth-child(3),
        .board-priority-table td:nth-child(3) { width: 11%; }
        .board-priority-table th:nth-child(4),
        .board-priority-table td:nth-child(4) { width: 8%; }
        .board-priority-table th:nth-child(5),
        .board-priority-table td:nth-child(5) { width: 6%; text-align: right; }
        .board-priority-table th:nth-child(6),
        .board-priority-table td:nth-child(6) { width: 6%; text-align: right; }
        .board-priority-table th:nth-child(7),
        .board-priority-table td:nth-child(7) { width: 10%; }
        .board-priority-table th:nth-child(8),
        .board-priority-table td:nth-child(8) { width: 6%; text-align: right; }
        .board-priority-table th:nth-child(9),
        .board-priority-table td:nth-child(9) { width: 7%; text-align: right; }
        .board-priority-table th:nth-child(10),
        .board-priority-table td:nth-child(10) { width: 15%; }

        .table-footnote {
            margin-top: 4px;
            font-size: 8px;
            color: #64748b;
        }

        .board-narrative-table {
            page-break-inside: avoid;
        }

        .empty {
            border: 1px dashed #c9d6e8;
            padding: 10px;
            color: #64748b;
            background: #f8fbff;
        }

        .page-break {
            page-break-after: always;
        }

        .heatmap-grid {
            margin-top: 6px;
            table-layout: fixed;
        }

        .heatmap-grid td,
        .heatmap-grid th {
            border: 1px solid #bfc6cf;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .heatmap-axis {
            background: #edf2f8;
            font-weight: 700;
            width: 26px;
            color: #0f2f4f;
        }

        .heat-cell {
            height: 54px;
        }

        .heat-level {
            font-weight: 700;
            font-size: 8px;
            line-height: 1.2;
        }

        .heat-score {
            font-weight: 700;
            font-size: 10px;
            margin-top: 3px;
        }

        .heat-count {
            font-size: 8px;
            margin-top: 2px;
        }

        .level-low { background: #00b050; color: #fff; }
        .level-low-moderate { background: #8cc84b; color: #111; }
        .level-moderate { background: #fff200; color: #111; }
        .level-moderate-high { background: #ffc000; color: #111; }
        .level-high { background: #ff0000; color: #fff; }

        .axis-note {
            margin-top: 6px;
            font-size: 8.8px;
            color: #334155;
        }
    </style>
</head>
<body>
    @php
        $heatLevelClass = function ($level) {
            switch ($level) {
                case 'Low':
                    return 'level-low';
                case 'Low to Moderate':
                    return 'level-low-moderate';
                case 'Moderate':
                    return 'level-moderate';
                case 'Moderate to High':
                    return 'level-moderate-high';
                case 'High':
                    return 'level-high';
                default:
                    return '';
            }
        };

        $sectionDescriptions = [
            'board_executive_summary' => 'Ringkasan metrik inti untuk pembacaan cepat oleh Board.',
            'board_status_chart' => 'Distribusi jumlah risiko per status saat ini.',
            'board_category_chart' => 'Distribusi eksposur risiko berdasarkan kategori utama.',
            'board_trend_chart' => 'Perubahan rata-rata skor inherent dan residual dalam periode terakhir.',
            'board_priority_table' => 'Daftar risiko prioritas tertinggi untuk fokus eskalasi dan tindakan.',
            'board_narrative' => 'Arahan manajemen berbasis indikator utama risiko.',
            'monitoring_overview' => 'Ringkasan performa monitoring dan kontrol.',
            'heatmap_dashboard' => 'Pemetaan posisi risiko pada matriks probabilitas versus dampak.',
        ];

        $resolveHeadings = function ($section, array $fallback = []) {
            $headings = is_array($section['headings'] ?? null) ? array_values($section['headings']) : [];
            if (!empty($headings)) {
                return $headings;
            }

            $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
            if (!empty($rows) && is_array($rows[0] ?? null)) {
                $columnCount = count($rows[0]);
                if ($columnCount > 0) {
                    $generated = [];
                    for ($i = 1; $i <= $columnCount; $i++) {
                        $generated[] = 'Column ' . $i;
                    }

                    return $generated;
                }
            }

            return $fallback;
        };

        $resolveTableClass = function ($section, array $headings = []) {
            $sectionData = is_array($section) ? $section : [];
            $resolvedHeadings = !empty($headings) ? $headings : (is_array($sectionData['headings'] ?? null) ? array_values($sectionData['headings']) : []);
            $columnCount = count($resolvedHeadings);

            if ($columnCount === 0) {
                $columnCount = \App\Support\PdfReportLayout::columnCountForSection($sectionData);
            }

            $densityClass = \App\Support\PdfReportLayout::tableDensityClass(max(1, $columnCount));

            return trim('data-table ' . $densityClass);
        };

        $hasRenderableContent = function ($section) {
            $type = (string) ($section['type'] ?? '');
            $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
            $kpiCards = is_array($section['kpi_cards'] ?? null) ? $section['kpi_cards'] : [];
            $hasInsight = trim((string) ($section['insight'] ?? '')) !== '';

            if ($type === 'heatmap_dashboard') {
                $matrix = is_array($section['matrix'] ?? null) ? $section['matrix'] : [];
                $eventRows = is_array($section['event_rows'] ?? null) ? $section['event_rows'] : [];

                return !empty($matrix) || !empty($eventRows) || $hasInsight;
            }

            if ($type === 'monitoring_overview' || $type === 'board_executive_summary') {
                return !empty($rows) || !empty($kpiCards) || $hasInsight;
            }

            return !empty($rows) || $hasInsight;
        };

        $renderableSections = [];
        foreach ((array) $sections as $candidateSection) {
            if ($hasRenderableContent($candidateSection)) {
                $renderableSections[] = $candidateSection;
            }
        }
    @endphp

    <div class="doc-header">
        <div class="left">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="height:44px;" />
            @else
                <h1>{{ $title }}</h1>
            @endif
        </div>
        <div class="right">
            <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.05em; color:#64748b;">Risk Management System</div>
            <div style="font-size:12px; font-weight:700; color:#0f2f4f;">{{ $title }}</div>
            <div style="margin-top:2px;">Generated: {{ $generatedAt }}</div>
        </div>
        <div style="clear:both"></div>
    </div>

    <div class="doc-footer">
        <div style="float:left;">{{ $title }}</div>
        <div style="float:right;">Page <span class="page-number"></span></div>
        <div style="clear:both"></div>
    </div>

    <div class="report-cover">
        <div class="cover-title">{{ $title }}</div>
        <table class="meta-table">
            <tr>
                <td class="meta-key">Generated At</td>
                <td>{{ $generatedAt }}</td>
                <td class="meta-key">Section Count</td>
                <td>{{ count($renderableSections) }}</td>
            </tr>
        </table>
    </div>

    @foreach($renderableSections as $section)
        @php
            $sectionType = (string) ($section['type'] ?? '');
            $sectionSubtitle = trim((string) ($section['subtitle'] ?? ($sectionDescriptions[$sectionType] ?? '')));
            $sectionClass = in_array($sectionType, ['board_narrative'], true)
                ? 'section section-keep-together'
                : 'section';
        @endphp
        <div class="{{ $sectionClass }}">
            <h2>{{ $section['title'] }}</h2>
            @if($sectionSubtitle !== '')
                <div class="section-subtitle">{{ $sectionSubtitle }}</div>
            @endif

            @if($sectionType === 'monitoring_overview' || $sectionType === 'board_executive_summary')
                @php
                    $kpiCards = is_array($section['kpi_cards'] ?? null) ? $section['kpi_cards'] : [];
                    $overviewRows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                    $overviewHeadings = $resolveHeadings($section, ['Metric', 'Value']);
                    $overviewTableClass = $resolveTableClass($section, $overviewHeadings);
                @endphp

                @if(!empty($kpiCards))
                    <table class="kpi-table">
                        <tbody>
                            @foreach(array_chunk($kpiCards, 4) as $kpiChunk)
                                <tr>
                                    @foreach($kpiChunk as $kpi)
                                        @php
                                            $tone = trim((string) ($kpi['tone'] ?? 'blue'));
                                            $allowedTone = in_array($tone, ['blue', 'red', 'amber', 'green'], true) ? $tone : 'blue';
                                        @endphp
                                        <td class="kpi-cell kpi-tone-{{ $allowedTone }}">
                                            <div class="kpi-label">{{ $kpi['label'] ?? '-' }}</div>
                                            <div class="kpi-value">{{ $kpi['value'] ?? '0' }}</div>
                                        </td>
                                    @endforeach
                                    @for($i = count($kpiChunk); $i < 4; $i++)
                                        <td class="kpi-cell">&nbsp;</td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if(!empty($overviewRows))
                    <table class="{{ $overviewTableClass }}">
                        <thead>
                            <tr>
                                @foreach($overviewHeadings as $heading)
                                    <th>{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overviewRows as $row)
                                <tr>
                                    @foreach($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty">No data available.</div>
                @endif
                @if(!empty($section['insight']))
                    <div class="board-insight">{{ $section['insight'] }}</div>
                @endif
            @elseif($sectionType === 'board_status_chart' || $sectionType === 'board_category_chart')
                @php
                    $chartRows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                    $chartHeadings = $resolveHeadings($section, ['Label', 'Count']);
                    $maxValue = 0;
                    foreach ($chartRows as $chartRow) {
                        $value = isset($chartRow[1]) ? (float) $chartRow[1] : 0;
                        if ($value > $maxValue) {
                            $maxValue = $value;
                        }
                    }
                    if ($maxValue <= 0) {
                        $maxValue = 1;
                    }
                    $barTones = ['blue', 'green', 'amber', 'red'];
                @endphp
                <div class="board-chart-block">
                    <div class="board-chart-title">{{ $section['title'] }}</div>
                    @if(!empty($chartRows))
                        <table class="board-bar-table">
                            <thead>
                                <tr>
                                    <th style="width:130px;">{{ $chartHeadings[0] ?? 'Label' }}</th>
                                    <th>Distribution</th>
                                    <th style="width:55px; text-align:right;">{{ $chartHeadings[1] ?? 'Count' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chartRows as $index => $chartRow)
                                    @php
                                        $label = (string) ($chartRow[0] ?? '-');
                                        $value = (float) ($chartRow[1] ?? 0);
                                        $percent = max(0, min(100, ($value / $maxValue) * 100));
                                        $tone = $barTones[$index % count($barTones)];
                                    @endphp
                                    <tr>
                                        <td class="board-bar-label">{{ $label }}</td>
                                        <td>
                                            <div class="board-bar-track">
                                                <div class="board-bar-fill board-tone-{{ $tone }}" style="width: {{ number_format($percent, 2, '.', '') }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="board-bar-value">{{ number_format($value, 0, '.', ',') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty">No chart data available.</div>
                    @endif
                </div>
                @if(!empty($section['insight']))
                    <div class="board-insight">{{ $section['insight'] }}</div>
                @endif
            @elseif($sectionType === 'board_trend_chart')
                @php
                    $trendRows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                    $trendHeadings = $resolveHeadings($section, ['Period', 'Avg Inherent', 'Avg Residual']);
                @endphp
                <div class="board-chart-block">
                    <div class="board-chart-title">{{ $section['title'] }}</div>
                    @if(!empty($trendRows))
                        <table class="board-trend-table">
                            <thead>
                                <tr>
                                    <th>{{ $trendHeadings[0] ?? 'Period' }}</th>
                                    <th>{{ $trendHeadings[1] ?? 'Avg Inherent' }}</th>
                                    <th>{{ $trendHeadings[2] ?? 'Avg Residual' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trendRows as $trendRow)
                                    <tr>
                                        <td>{{ $trendRow[0] ?? '-' }}</td>
                                        <td>{{ $trendRow[1] ?? '0.00' }}</td>
                                        <td>{{ $trendRow[2] ?? '0.00' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty">No trend data available.</div>
                    @endif
                </div>
                @if(!empty($section['insight']))
                    <div class="board-insight">{{ $section['insight'] }}</div>
                @endif
            @elseif($sectionType === 'board_priority_table')
                @php
                    $priorityRows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                    $priorityHeadings = $resolveHeadings($section, [
                        'Risk ID',
                        'Risk Event',
                        'Owner',
                        'Status',
                        'Inherent',
                        'Residual',
                        'Residual Level',
                        'Gap',
                        'Treatments',
                        'Nearest Deadline',
                    ]);
                    $priorityTableClass = trim($resolveTableClass($section, $priorityHeadings) . ' board-priority-table');
                @endphp
                @if(!empty($priorityRows))
                    <table class="{{ $priorityTableClass }}">
                        <thead>
                            <tr>
                                @foreach($priorityHeadings as $heading)
                                    <th>{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($priorityRows as $row)
                                <tr>
                                    @foreach($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="table-footnote">Urutan watchlist: residual score tertinggi, lalu inherent score, lalu jumlah treatment.</div>
                @else
                    <div class="empty">No watchlist data available.</div>
                @endif
                @if(!empty($section['insight']))
                    <div class="board-insight">{{ $section['insight'] }}</div>
                @endif
            @elseif($sectionType === 'board_narrative')
                @php
                    $narrativeRows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                    $narrativeHeadings = $resolveHeadings($section, ['Theme', 'Management Recommendation']);
                    $narrativeTableClass = trim($resolveTableClass($section, $narrativeHeadings) . ' board-narrative-table');
                @endphp
                <h3>Management Recommendation Matrix</h3>
                @if(!empty($narrativeRows))
                    <table class="{{ $narrativeTableClass }}">
                        <thead>
                            <tr>
                                <th style="width: 190px;">{{ $narrativeHeadings[0] ?? 'Theme' }}</th>
                                <th>{{ $narrativeHeadings[1] ?? 'Management Recommendation' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($narrativeRows as $row)
                                <tr>
                                    <td>{{ $row[0] ?? '-' }}</td>
                                    <td>{{ $row[1] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="table-footnote">Kolom kiri menjelaskan tema risiko, kolom kanan berisi arahan aksi manajemen.</div>
                @else
                    <div class="empty">No narrative available.</div>
                @endif
            @elseif($sectionType === 'heatmap_dashboard')
                @php
                    $matrix = $section['matrix'] ?? [];
                    $eventRows = $section['event_rows'] ?? [];
                    $heatmapEventTableClass = trim($resolveTableClass($section, ['Code', 'Category', 'Risk Event', 'Risk Owner']));
                @endphp

                @if(!empty($matrix))
                    <h3>Heatmap Matrix (5x5)</h3>
                    <table class="heatmap-grid">
                        <tbody>
                            @for($probability = 5; $probability >= 1; $probability--)
                                <tr>
                                    <th class="heatmap-axis">{{ $probability }}</th>
                                    @for($impact = 1; $impact <= 5; $impact++)
                                        @php
                                            $cell = $matrix[$probability][$impact] ?? [
                                                'score' => '-',
                                                'level' => '-',
                                                'inherent_count' => 0,
                                                'residual_count' => 0,
                                            ];
                                        @endphp
                                        <td class="heat-cell {{ $heatLevelClass($cell['level']) }}">
                                            <div class="heat-level">{{ $cell['level'] }}</div>
                                            <div class="heat-score">{{ $cell['score'] ?? '-' }}</div>
                                            <div class="heat-count">I: {{ $cell['inherent_count'] }} | R: {{ $cell['residual_count'] }}</div>
                                        </td>
                                    @endfor
                                </tr>
                            @endfor
                            <tr>
                                <th class="heatmap-axis"></th>
                                @for($impact = 1; $impact <= 5; $impact++)
                                    <th class="heatmap-axis">{{ $impact }}</th>
                                @endfor
                            </tr>
                        </tbody>
                    </table>
                    <div class="axis-note">Y: Likelihood (1-5), X: Impact (1-5), I: Inherent count, R: Residual count</div>
                @endif

                <h3>Heatmap Risk Event Mapping</h3>
                @if(!empty($eventRows))
                    <table class="{{ $heatmapEventTableClass }}">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Code</th>
                                <th style="width: 120px;">Category</th>
                                <th>Risk Event</th>
                                <th style="width: 120px;">Risk Owner</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eventRows as $row)
                                <tr>
                                    <td>{{ $row['code'] }}</td>
                                    <td>{{ $row['category'] }}</td>
                                    <td>{{ $row['risk_event'] }}</td>
                                    <td>{{ $row['risk_owner'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty">No risk data available.</div>
                @endif
            @elseif(!empty($section['rows']))
                @php
                    $defaultHeadings = $resolveHeadings($section);
                    $defaultTableClass = $resolveTableClass($section, $defaultHeadings);
                @endphp
                <table class="{{ $defaultTableClass }}">
                    <thead>
                        <tr>
                            @foreach($defaultHeadings as $heading)
                                <th>{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['rows'] as $row)
                            <tr>
                                @foreach($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if(!empty($section['insight']))
                    <div class="board-insight">{{ $section['insight'] }}</div>
                @endif
            @else
                <div class="empty">No data available.</div>
            @endif
        </div>
    @endforeach
    @if(empty($renderableSections))
        <div class="section">
            <h2>No Report Data</h2>
            <div class="empty">Tidak ada data yang bisa ditampilkan untuk filter report saat ini.</div>
        </div>
    @endif
</body>
</html>
