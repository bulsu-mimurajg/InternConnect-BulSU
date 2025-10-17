<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placed Students Report</title>
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
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            
            border-left: 4px solid #e67e22;
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
             
             
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        .section h2 { 
            background: #f8f9fa; 
            color: white; 
            margin: 0; 
            padding: 20px; 
            font-size: 20px; 
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
        .stat-item {
            margin-bottom: 15px;
            position: relative;
            padding-left: 20px;
        }
        
        .stat-number { 
            font-size: 32px; 
            font-weight: 700; 
            color: #e67e22; 
            margin-bottom: 5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
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
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background: white;
            
            overflow: hidden;
            
        }
        th { 
            background: #f8f9fa; 
            color: white; 
            padding: 15px 12px; 
            text-align: left; 
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td { 
            padding: 12px; 
            border-bottom: 1px solid #e9ecef; 
            font-size: 13px;
            vertical-align: middle;
        }
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        
        
        /* Column widths */
        .col-student { width: 12%; }
        .col-name { width: 18%; }
        .col-section { width: 12%; }
        .col-company { width: 18%; }
        .col-position { width: 15%; }
        .col-department { width: 12%; }
        .col-score { width: 8%; }
        .col-status { width: 8%; }
        .col-date { width: 8%; }
        
        /* Status badges */
        .status-placed { 
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
                <h1>Placed Students Report</h1>
            </div>
            
            <div class="generation-info">
                <p>Section: {{ $sectionName }} | Generated on {{ $generatedAt }}</p>
            </div>

            <!-- Section Statistics -->
            @if(isset($overviewStats) && count($overviewStats) > 0)
            <div class="section">
                <h2>Placement Overview</h2>
                <div class="section-content">
                    <div class="stats">
                        @foreach($overviewStats as $stat)
                        <div class="stat-item">
                            <div class="stat-number">{{ $stat['value'] }}</div>
                            <div class="stat-label">{{ $stat['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Placed Students List -->
            @if(isset($placedStudents) && count($placedStudents) > 0)
            <div class="section page-break">
                <h2>Placed Students Details</h2>
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
                            @foreach($placedStudents as $student)
                            <tr>
                                <td class="col-student">{{ $student['student_number'] ?? 'N/A' }}</td>
                                <td class="col-name">{{ $student['name'] }}</td>
                                <td class="col-section">{{ $student['section'] }}</td>
                                <td class="col-company">{{ $student['hte_name'] }}</td>
                                <td class="col-position">{{ $student['position_title'] }}</td>
                                <td class="col-department">{{ $student['department'] ?? 'N/A' }}</td>
                                <td class="col-score">{{ $student['compatibility_score'] }}</td>
                                <td class="col-status">
                                    <span class="status-{{ strtolower($student['status']) }}">
                                        {{ ucfirst($student['status']) }}
                                    </span>
                                </td>
                                <td class="col-date">{{ $student['placement_date'] ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="section page-break">
                <h2>Placed Students Details</h2>
                <div class="section-content">
                    <p style="text-align: center; color: #6c757d; font-style: italic; padding: 40px;">
                        No placed students found for the selected section.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>