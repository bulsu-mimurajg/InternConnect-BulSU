<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTE Performance Report</title>
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
            margin-bottom: 25px;
            background: #fafafa;
            padding: 20px;
            display: flex;
            gap: 20px;
        }
        .stats-column {
            flex: 1;
            width: 50%;
        }
        .stat-item {
            margin-bottom: 15px;
            position: relative;
            padding-left: 20px;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            font-weight: 600;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
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
            .stats {
            margin-bottom: 25px;
            background: #fafafa;
            padding: 20px;
            display: flex;
            gap: 20px;
        }
        .stats-column {
            flex: 1;
            width: 50%;
        }
        }
    
        
        
        .stat-item {
            margin-bottom: 15px;
            position: relative;
            padding-left: 20px;
        }
        .stat-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            width: 8px;
            height: 1px;
            background: #e67e22;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            font-weight: 600;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
    
        @media print {
            .stats { display: flex; }
            .stats-column { width: 50%; }
        }
        .stats-column {
            flex: 1;
            width: 50%;
        }
        }
        @media (max-width: 768px) {
            .stats { flex-direction: column; }
            .stats-column { width: 100%; }
        }
        .stats-column {
            flex: 1;
            width: 50%;
        }
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
                <h1>HTE Performance Report</h1>
                <div class="subtitle">{{ $hte_name ?? 'All HTEs' }}</div>
                <div class="date">Generated on {{ now()->format('F d, Y \a\t g:i A') }}</div>
            </div>

            <!-- Statistics Overview -->
            <div class="section">
                <h2 class="section-title">Statistics Overview</h2>
                <div class="stats">
                    @foreach($stats as $stat)
                        <div class="stat-item">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- HTE Performance Details -->
            <div class="section">
                <h2 class="section-title">HTE Performance Details</h2>
                @if($htes->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>HTE Company</th>
                                <th>Total Internships</th>
                                <th>Total Slots</th>
                                <th>Used Slots</th>
                                <th>Utilization Rate</th>
                                <th>Placed Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($htes as $hte)
                                @php
                                    $totalSlots = $hte->internships->sum('slot_count');
                                    $usedSlots = $hte->internships->sum(function($internship) {
                                        return $internship->studentPlacements->where('status', 'approved')->count();
                                    });
                                    $utilizationRate = $totalSlots > 0 ? ($usedSlots / $totalSlots) * 100 : 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $hte->company_name }}</strong></td>
                                    <td>{{ $hte->internships->count() }}</td>
                                    <td>{{ $totalSlots }}</td>
                                    <td>{{ $usedSlots }}</td>
                                    <td>
                                        <span style="color: {{ $utilizationRate >= 70 ? '#27ae60' : ($utilizationRate >= 40 ? '#f39c12' : '#e74c3c') }}; font-weight: bold;">
                                            {{ number_format($utilizationRate, 1) }}%
                                        </span>
                                    </td>
                                    <td>{{ $usedSlots }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align: center; color: #7f8c8d; font-style: italic; padding: 20px;">
                        No HTE data found for the selected criteria.
                    </p>
                @endif
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>This report was generated by the InternConnect BSIT system.</p>
                <p>For questions or concerns, please contact the system administrator.</p>
            </div>
        </div>
    </div>
</body>
</html>
