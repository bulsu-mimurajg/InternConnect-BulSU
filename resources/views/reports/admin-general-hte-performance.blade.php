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
            text-align: center; 
            margin-bottom: 40px; 
            padding: 20px 0;
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 { 
            color: white; 
            margin: 0; 
            font-size: 32px; 
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .report-title {
            text-align: center;
            margin-bottom: 25px;
            padding: 30px 20px;
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            border: 2px solid #d35400;
        }
        .report-title h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 32px;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 1px;
        }
        .generation-info {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #e67e22;
        }
        .generation-info p {
            margin: 0;
            color: #495057;
            font-size: 14px;
            font-weight: 500;
            font-style: italic;
        }
        
        .section { 
            margin-bottom: 40px; 
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .section h2 { 
            color: #e67e22; 
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            margin: 0;
            padding: 20px 25px;
            font-size: 24px;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        .section-content {
            padding: 25px;
        }
        
        .stats { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); 
            border-radius: 12px; 
            margin-bottom: 25px; 
            border: 2px solid #e67e22;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats h3 { 
            margin-top: 0; 
            color: #2c3e50; 
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }
        .metric-group {
            margin-bottom: 30px;
        }
        .metric-group h4 {
            margin: 0 30px 15px 30px;
            color: #e67e22;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #e67e22;
            padding-bottom: 8px;
        }
        .stats-row {
            display: flex !important;
            flex-direction: row !important;
            gap: 20px !important;
            width: 100%;
            justify-content: space-between;
        }
        .stats-row .stat-item {
            flex: 1 !important;
            margin-bottom: 0 !important;
            min-width: 0 !important;
        }
        .stat-item { 
            text-align: center; 
            background: white;
            padding: 25px 12px;
            border-radius: 12px;
            border: 2px solid #f39c12;
        }
        .stat-value { 
            font-size: 32px; 
            font-weight: bold; 
            color: #e67e22; 
            margin-bottom: 12px;
            line-height: 1.2;
        }
        .stat-label { 
            font-size: 13px; 
            color: #495057; 
            text-transform: uppercase; 
            font-weight: 600;
            letter-spacing: 1px;
            line-height: 1.3;
        }
        
        table { 
            width: 100% !important; 
            max-width: 100% !important;
            border-collapse: collapse; 
            margin-top: 20px; 
            table-layout: fixed; 
            font-size: 10px; 
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td { 
            border: 1px solid #dee2e6; 
            padding: 6px 8px; 
            text-align: left; 
            word-wrap: break-word; 
            color: #000000 !important;
        }
        td {
            color: #000000 !important;
            background-color: #ffffff !important;
        }
        th { 
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%); 
            color: white;
            font-weight: bold; 
            font-size: 10px; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #fff3cd;
        }
        
        .col-company { width: 25%; }
        .col-contact { width: 18%; }
        .col-status { width: 10%; }
        .col-internships { width: 12%; }
        .col-slots { width: 12%; }
        .col-rate { width: 13%; }
        .col-date { width: 12%; }
        
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            color: #6c757d; 
            font-size: 12px; 
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border-top: 3px solid #e67e22;
        }
        .page-break { page-break-before: always; }
        
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
        
        /* Responsive adjustments */
        @media print {
            body { padding: 10px; }
            .section { box-shadow: none; }
            .stat-item:hover { transform: none; }
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
                <h1>HTE Performance Report</h1>
            </div>
            
            <div class="generation-info">
                <p>Generated on {{ $generatedAt }}</p>
            </div>

            <!-- System Statistics -->
            @if(isset($stats))
            <div class="section">
                <h2>System Statistics</h2>
                <div class="section-content">
                    <div class="stats">
                        <h3>HTE Performance Metrics</h3>
                        
                        <div class="metric-group">
                            <h4>Overall HTE Statistics</h4>
                            <table style="width: 100%; border-collapse: separate; border-spacing: 15px;">
                                <tr>
                                    <td style="width: 25%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $stats['totalHTEs'] }}</div>
                                            <div class="stat-label">Total HTEs</div>
                                        </div>
                                    </td>
                                    <td style="width: 25%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $stats['activeHTEs'] }}</div>
                                            <div class="stat-label">Active HTEs</div>
                                        </div>
                                    </td>
                                    <td style="width: 25%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $stats['totalInternships'] }}</div>
                                            <div class="stat-label">Total Internships</div>
                                        </div>
                                    </td>
                                    <td style="width: 25%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $stats['totalSlots'] }}</div>
                                            <div class="stat-label">Total Slots</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- HTE Performance List -->
            @if(isset($hteStats) && count($hteStats) > 0)
            <div class="section page-break">
                <h2>HTE Performance Details</h2>
                <div class="section-content">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-company">Company</th>
                                <th class="col-contact">Contact Person</th>
                                <th class="col-status">Status</th>
                                <th class="col-internships">Total Internships</th>
                                <th class="col-internships">Active Internships</th>
                                <th class="col-slots">Total Slots</th>
                                <th class="col-slots">Filled Slots</th>
                                <th class="col-rate">Utilization Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hteStats as $hte)
                            <tr>
                                <td class="col-company"><strong>{{ $hte['company_name'] }}</strong></td>
                                <td class="col-contact">{{ $hte['contact_person'] }}</td>
                                <td class="col-status">
                                    <span class="status-{{ $hte['is_submit'] ? 'active' : 'inactive' }}">
                                        {{ $hte['is_submit'] ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="col-internships">{{ $hte['totalInternships'] }}</td>
                                <td class="col-internships">{{ $hte['activeInternships'] }}</td>
                                <td class="col-slots">{{ $hte['totalSlots'] }}</td>
                                <td class="col-slots">{{ $hte['filledSlots'] }}</td>
                                <td class="col-rate">{{ $hte['utilizationRate'] }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="section page-break">
                <h2>HTE Performance Details</h2>
                <div class="section-content">
                    <div class="stats">
                        <p style="text-align: center; color: #6c757d; font-style: italic;">No HTE data available.</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="footer">
                <p>This report was generated automatically by the InternConnect System</p>
            </div>
        </div>
    </div>
</body>
</html>