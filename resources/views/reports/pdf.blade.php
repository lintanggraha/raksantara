<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 20px;
        }

        .meta {
            margin-bottom: 16px;
            color: #4b5563;
        }

        .section {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        .section h2 {
            margin: 0 0 8px;
            font-size: 14px;
            color: #1f2937;
        }

        .section h3 {
            margin: 10px 0 6px;
            font-size: 12px;
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 4px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #e5e7eb;
            font-size: 9px;
        }

        td {
            font-size: 9px;
        }

        .empty {
            border: 1px solid #d1d5db;
            padding: 10px;
            color: #6b7280;
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
            background: #f3f4f6;
            font-weight: 700;
            width: 26px;
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

        .level-low {
            background: #00b050;
            color: #fff;
        }

        .level-low-moderate {
            background: #8cc84b;
            color: #111;
        }

        .level-moderate {
            background: #fff200;
            color: #111;
        }

        .level-moderate-high {
            background: #ffc000;
            color: #111;
        }

        .level-high {
            background: #ff0000;
            color: #fff;
        }

        .axis-note {
            margin-top: 6px;
            font-size: 9px;
            color: #374151;
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
    @endphp

    <h1>{{ $title }}</h1>
    <div class="meta">Generated at: {{ $generatedAt }}</div>

    @foreach($sections as $section)
        <div class="section">
            <h2>{{ $section['title'] }}</h2>

            @if(($section['type'] ?? '') === 'heatmap_dashboard')
                @php
                    $matrix = $section['matrix'] ?? [];
                    $eventRows = $section['event_rows'] ?? [];
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
                    <table>
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
                <table>
                    <thead>
                        <tr>
                            @foreach($section['headings'] as $heading)
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
            @else
                <div class="empty">No data available.</div>
            @endif
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
