<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Placements Report</title>
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
            background: #f8f9fa;
            color: white;
            
            
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
            background: #f8f9fa;
            
            
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
            
            
            overflow: hidden;
        }
        .section h2 { 
            color: #e67e22; 
            background: #f8f9fa;
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
            margin-bottom: 15px;
            position: relative;
            padding-left: 20px;
        }
        .stat-item {
            margin-bottom: 15px;
            position: relative;
            padding-left: 20px;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            font-weight: 600;
        }
        
        table { 
            width: 100% !important; 
            max-width: 100% !important;
            border-collapse: collapse; 
            margin-top: 20px; 
            table-layout: fixed; 
            font-size: 10px; 
            background: white;
            
            overflow: hidden;
            
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
            background: #f8f9fa; 
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
        
        
        .col-student { width: 12%; }
        .col-name { width: 18%; }
        .col-section { width: 12%; }
        .col-company { width: 20%; }
        .col-position { width: 16%; }
        .col-department { width: 12%; }
        .col-score { width: 8%; }
        .col-status { width: 10%; }
        .col-date { width: 12%; }
        
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            color: #6c757d; 
            font-size: 12px; 
            padding: 20px;
            background: #f8f9fa;
            
            border-top: 3px solid #e67e22;
        }
        .page-break { page-break-before: always; }
        
        /* Status badges */
        .status-approved { 
            background-color: #d4edda; 
            color: #155724; 
            padding: 2px 6px; 
             
            font-weight: bold;
            font-size: 9px;
        }
        .status-pending { 
            background-color: #fff3cd; 
            color: #856404; 
            padding: 2px 6px; 
             
            font-weight: bold;
            font-size: 9px;
        }
        .status-rejected { 
            background-color: #f8d7da; 
            color: #721c24; 
            padding: 2px 6px; 
             
            font-weight: bold;
            font-size: 9px;
        }
        
        /* Responsive adjustments */
        @media print {
            body { padding: 10px; }
            .section {  }
            
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
        <!-- Document Header Image -->
        <div class="document-header">
            <img src="{{ public_path('images/document_header.png') }}" alt="Bulacan State University Header" style="width: 100%; height: auto; max-height: 200px; object-fit: contain;">
        </div>
        
        <div class="content-wrapper">
            <div class="report-title">
                <h1>All Placements Report</h1>
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
                        <h3>Placement Metrics</h3>
                        
                        <div class="metric-group">
                            <h4>Overall Placement Statistics</h4>
                            <table style="width: 100%; border-collapse: separate; border-spacing: 15px;">
                                <tr>
                                    <td style="width: 33.33%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $stats['totalStudents'] }}</div>
                                            <div class="stat-label">Total Students</div>
                                        </div>
                                    </td>
                                    <td style="width: 33.33%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $stats['placedStudents'] }}</div>
                                            <div class="stat-label">Placed Students</div>
                                        </div>
                                    </td>
                                    <td style="width: 33.33%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $stats['placementRate'] }}%</div>
                                            <div class="stat-label">Placement Rate</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- All Placements List -->
            @if(isset($placements) && count($placements) > 0)
            <div class="section page-break">
                <h2>All Placements Details</h2>
                <div class="section-content">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-student">Student Number</th>
                                <th class="col-name">Name</th>
                                <th class="col-section">Section</th>
                                <th class="col-company">Company</th>
                                <th class="col-position">Position</th>
                                <th class="col-department">Department</th>
                                <th class="col-score">Score</th>
                                <th class="col-status">Status</th>
                                <th class="col-date">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($placements as $placement)
                            <tr>
                                <td class="col-student"><strong>{{ $placement['student_number'] }}</strong></td>
                                <td class="col-name">{{ $placement['name'] }}</td>
                                <td class="col-section">{{ $placement['section'] }}</td>
                                <td class="col-company">{{ $placement['company'] }}</td>
                                <td class="col-position">{{ $placement['position'] }}</td>
                                <td class="col-department">{{ $placement['department'] }}</td>
                                <td class="col-score">{{ $placement['compatibility_score'] }}</td>
                                <td class="col-status">
                                    <span class="status-{{ $placement['status'] }}">
                                        {{ ucfirst($placement['status']) }}
                                    </span>
                                </td>
                                <td class="col-date">{{ $placement['placement_date'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="section page-break">
                <h2>All Placements Details</h2>
                <div class="section-content">
                    <div class="stats">
                        <p style="text-align: center; color: #6c757d; font-style: italic;">No placements found in the system.</p>
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