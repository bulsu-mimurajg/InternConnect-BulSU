<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Assessment Report</title>
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
            border-radius: 8px;
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
            border-radius: 12px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        .section h2 { 
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); 
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
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .stat-item { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); 
            padding: 20px; 
            border-radius: 10px; 
            text-align: center; 
            border: 1px solid #dee2e6;
            transition: transform 0.2s ease;
        }
        .stat-item:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-number { 
            font-size: 32px; 
            font-weight: 700; 
            color: #e67e22; 
            margin-bottom: 5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .stat-label { 
            font-size: 14px; 
            color: #6c757d; 
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th { 
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); 
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
        tr:hover { 
            background-color: #e3f2fd; 
            transition: background-color 0.2s ease;
        }
        
        /* Column widths */
        .col-student { width: 15%; }
        .col-name { width: 20%; }
        .col-section { width: 15%; }
        .col-assessment { width: 15%; }
        .col-score { width: 10%; }
        .col-percentage { width: 10%; }
        .col-status { width: 10%; }
        .col-date { width: 10%; }
        
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
        
        .assessment-yes { 
            background-color: #d4edda; 
            color: #155724; 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-weight: bold;
            font-size: 9px;
        }
        .assessment-no { 
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
                <h1>Student Assessment Report</h1>
            </div>
            
            <div class="generation-info">
                <p>Section: {{ $sectionName }} | Generated on {{ $generatedAt }}</p>
            </div>

            <!-- Section Statistics -->
            @if(isset($overviewStats) && count($overviewStats) > 0)
            <div class="section">
                <h2>Assessment Overview</h2>
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

            <!-- Students Assessment List -->
            @if(isset($studentProgress) && count($studentProgress) > 0)
            <div class="section page-break">
                <h2>Student Assessment Details</h2>
                <div class="section-content">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-student">Student Number</th>
                                <th class="col-name">Name</th>
                                <th class="col-section">Section</th>
                                <th class="col-assessment">Assessment Status</th>
                                <th class="col-score">Total Score</th>
                                <th class="col-percentage">Percentage</th>
                                <th class="col-status">Student Status</th>
                                <th class="col-date">Submitted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentProgress as $student)
                            <tr>
                                <td class="col-student">{{ $student['student_number'] ?? 'N/A' }}</td>
                                <td class="col-name">{{ $student['name'] }}</td>
                                <td class="col-section">{{ $student['section'] }}</td>
                                <td class="col-assessment">
                                    <span class="assessment-{{ $student['hasAssessment'] ? 'yes' : 'no' }}">
                                        {{ $student['hasAssessment'] ? 'Completed' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="col-score">{{ $student['score'] ?? 0 }}</td>
                                <td class="col-percentage">{{ $student['percentage'] ?? 0 }}%</td>
                                <td class="col-status">
                                    <span class="status-{{ strtolower($student['status']) }}">
                                        {{ ucfirst($student['status']) }}
                                    </span>
                                </td>
                                <td class="col-date">{{ $student['submittedAt'] ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="section page-break">
                <h2>Student Assessment Details</h2>
                <div class="section-content">
                    <p style="text-align: center; color: #6c757d; font-style: italic; padding: 40px;">
                        No student assessment data found for the selected section.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
