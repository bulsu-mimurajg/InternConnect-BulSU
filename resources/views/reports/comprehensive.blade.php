<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: #2c3e50;
            background-color: #ffffff;
            line-height: 1.6;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
        }
        .content-wrapper {
            max-width: 800px !important;
            margin: 0 auto !important;
            width: 100% !important;
        }
        .content-wrapper * {
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .content-wrapper .section,
        .content-wrapper .stats,
        .content-wrapper table,
        .content-wrapper .metric-group {
            max-width: 100% !important;
            width: 100% !important;
        }

        .document-header {
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 3px solid #e67e22;
            padding-bottom: 20px;
        }
        .document-header img {
            max-width: 100%;
            height: auto;
            max-height: 180px;
        }

        .header {
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: bold;
        }
        .header .subtitle {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .header .date {
            color: #95a5a6;
            font-size: 14px;
        }

        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            color: #2c3e50;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #e67e22;
            padding-bottom: 5px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #e67e22;
            text-align: center;
        }
        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        th {
            background-color: #e67e22;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e8f4f8;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #7f8c8d;
            font-size: 12px;
            border-top: 1px solid #ecf0f1;
            padding-top: 20px;
        }

        @media print {
            body { margin: 0; padding: 15px; }
            .section { page-break-inside: avoid; }
            .stats { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-wrapper">
            <!-- Document Header -->
            <div class="document-header">
                <img src="{{ public_path('images/document_header.png') }}" alt="BulSU Document Header" style="max-width: 100%; height: auto;">
            </div>

            <!-- Report Header -->
            <div class="header">
                <h1>Comprehensive System Report</h1>
                <div class="subtitle">Overall System Performance and Statistics</div>
                <div class="date">Generated on {{ now()->format('F d, Y \a\t g:i A') }}</div>
            </div>

            <!-- Overall Statistics -->
            <div class="section">
                <h2 class="section-title">System Overview</h2>
                <div class="stats">
                    @foreach($overallStatsFormatted as $stat)
                        <div class="stat-card">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Student Statistics -->
            <div class="section">
                <h2 class="section-title">Student Statistics</h2>
                <div class="stats">
                    @foreach($studentStats as $stat)
                        <div class="stat-card">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- HTE Statistics -->
            <div class="section">
                <h2 class="section-title">HTE Statistics</h2>
                <div class="stats">
                    @foreach($hteStats as $stat)
                        <div class="stat-card">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Placement Statistics -->
            <div class="section">
                <h2 class="section-title">Placement Statistics</h2>
                <div class="stats">
                    @foreach($placementStats as $stat)
                        <div class="stat-card">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Endorsement Statistics -->
            <div class="section">
                <h2 class="section-title">Endorsement Statistics</h2>
                <div class="stats">
                    @foreach($endorsementStats as $stat)
                        <div class="stat-card">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Performing Sections -->
            @if(isset($sectionStats) && $sectionStats->count() > 0)
            <div class="section">
                <h2 class="section-title">Top Performing Sections</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Total Students</th>
                            <th>Average Score</th>
                            <th>Placement Rate</th>
                            <th>Endorsement Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sectionStats as $section)
                            <tr>
                                <td><strong>{{ $section['section_name'] }}</strong></td>
                                <td>{{ $section['total_students'] }}</td>
                                <td>
                                    <span style="color: {{ $section['average_score'] >= 80 ? '#27ae60' : ($section['average_score'] >= 60 ? '#f39c12' : '#e74c3c') }}; font-weight: bold;">
                                        {{ number_format($section['average_score'], 1) }}
                                    </span>
                                </td>
                                <td>
                                    <span style="color: {{ $section['placement_rate'] >= 70 ? '#27ae60' : ($section['placement_rate'] >= 40 ? '#f39c12' : '#e74c3c') }}; font-weight: bold;">
                                        {{ number_format($section['placement_rate'], 1) }}%
                                    </span>
                                </td>
                                <td>
                                    <span style="color: {{ $section['endorsement_rate'] >= 70 ? '#27ae60' : ($section['endorsement_rate'] >= 40 ? '#f39c12' : '#e74c3c') }}; font-weight: bold;">
                                        {{ number_format($section['endorsement_rate'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Top Performing HTEs -->
            @if(isset($htePerformanceStats) && $htePerformanceStats->count() > 0)
            <div class="section">
                <h2 class="section-title">Top Performing HTEs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>HTE Company</th>
                            <th>Total Internships</th>
                            <th>Total Slots</th>
                            <th>Utilization Rate</th>
                            <th>Placed Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($htePerformanceStats as $hte)
                            <tr>
                                <td><strong>{{ $hte['company_name'] }}</strong></td>
                                <td>{{ $hte['total_internships'] }}</td>
                                <td>{{ $hte['total_slots'] }}</td>
                                <td>
                                    <span style="color: {{ $hte['utilization_rate'] >= 70 ? '#27ae60' : ($hte['utilization_rate'] >= 40 ? '#f39c12' : '#e74c3c') }}; font-weight: bold;">
                                        {{ number_format($hte['utilization_rate'], 1) }}%
                                    </span>
                                </td>
                                <td>{{ $hte['placed_students'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Footer -->
            <div class="footer">
                <p>This report was generated by the InternConnect BSIT system.</p>
                <p>For questions or concerns, please contact the system administrator.</p>
            </div>
        </div>
    </div>
</body>
</html>
