<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportName ?? 'report' }}</title>
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
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            background: #f8f9fa;
            color: #2c3e50;
            border: 1px solid #ddd;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .report-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .report-title h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .generation-info {
            text-align: left;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        .generation-info p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .section {
            margin-bottom: 30px;
            background: white;
            border: 1px solid #ddd;
            padding: 0;
        }
        .section h2 {
            background: #f8f9fa;
            color: #2c3e50;
            margin: 0;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 1px solid #ddd;
        }
        .section-content {
            padding: 25px;
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

        .metric-group {
            margin-bottom: 20px;
        }

        .metric-group h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid #e67e22;
            padding-bottom: 5px;
        }

        .metric-group h4 {
            color: #495057;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            color: #2c3e50;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Status badges */
        .status-active {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-endorsed {
            background-color: #cce5ff;
            color: #004085;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-approved {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
        }

        /* Responsive adjustments */
        @media print {
            body { padding: 10px; }
            .section { box-shadow: none; }
            .stat-item:hover { transform: none; }
            .stats { display: flex; }
            .stats-column { width: 50%; }
        }
        @media (max-width: 768px) {
            .stats { flex-direction: column; }
            .stats-column { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Document Header Image -->
        <div class="document-header">
            <img src="{{ public_path('images/document_header.png') }}" alt="Bulacan State University Header" style="width: 100%; height: auto; max-height: 200px; object-fit: contain;">
        </div>

        <div class="content-wrapper">
            <div class="report-title">
                <h1>{{ $reportName ?? 'report' }}</h1>
            </div>

            <div class="generation-info">
                <p>
                    @if(isset($section_name))
                        Section: {{ $section_name }} |
                    @endif
                    @if(isset($hte_name))
                        HTE: {{ $hte_name }} |
                    @endif
                    Generated on {{ $generatedAt }}
                </p>
            </div>

            @yield('content')
        </div>
    </div>
</body>
</html>
