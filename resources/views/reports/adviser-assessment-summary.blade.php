<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Summary Report</title>
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
            font-size: 11px; 
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td { 
            border: 1px solid #dee2e6; 
            padding: 8px 10px; 
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
            font-size: 11px; 
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
        
        .col-metric { width: 50%; }
        .col-value { width: 50%; }
        .col-level { width: 30%; }
        .col-range { width: 25%; }
        .col-count { width: 45%; }
        
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
                <h1>Assessment Summary Report</h1>
            </div>
            
            <div class="generation-info">
                <p>Section: {{ $sectionName }} | Generated on {{ $generatedAt }}</p>
            </div>

            <!-- Overview Statistics -->
            <div class="section">
                <h2>Overview Statistics</h2>
                <div class="section-content">
                    <div class="stats">
                        <h3>Assessment Performance Metrics</h3>
                        
                        <div class="metric-group">
                            <h4>Student Assessment Metrics</h4>
                            <table style="width: 100%; border-collapse: separate; border-spacing: 15px;">
                                <tr>
                                    <td style="width: 25%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $overviewStats['totalStudents'] }}</div>
                                            <div class="stat-label">Total Students</div>
                                        </div>
                                    </td>
                                    <td style="width: 25%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $overviewStats['completedAssessments'] }}</div>
                                            <div class="stat-label">Completed Assessments</div>
                                        </div>
                                    </td>
                                    <td style="width: 25%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $overviewStats['completionRate'] }}%</div>
                                            <div class="stat-label">Completion Rate</div>
                                        </div>
                                    </td>
                                    <td style="width: 25%; vertical-align: top;">
                                        <div class="stat-item">
                                            <div class="stat-value">{{ $overviewStats['averageScore'] }}</div>
                                            <div class="stat-label">Average Score</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assessment Statistics -->
            <div class="section page-break">
                <h2>Assessment Statistics</h2>
                <div class="section-content">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-metric">Metric</th>
                                <th class="col-value">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="col-metric"><strong>Total Students</strong></td>
                                <td class="col-value">{{ $overviewStats['totalStudents'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-metric"><strong>Completed Assessments</strong></td>
                                <td class="col-value">{{ $overviewStats['completedAssessments'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-metric"><strong>Pending Students</strong></td>
                                <td class="col-value">{{ $overviewStats['pendingStudents'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-metric"><strong>Completion Rate</strong></td>
                                <td class="col-value">{{ $overviewStats['completionRate'] }}%</td>
                            </tr>
                            <tr>
                                <td class="col-metric"><strong>Average Score</strong></td>
                                <td class="col-value">{{ $overviewStats['averageScore'] }}/5.0</td>
                            </tr>
                            <tr>
                                <td class="col-metric"><strong>Highest Score</strong></td>
                                <td class="col-value">{{ $overviewStats['highestScore'] }}/5.0</td>
                            </tr>
                            <tr>
                                <td class="col-metric"><strong>Lowest Score</strong></td>
                                <td class="col-value">{{ $overviewStats['lowestScore'] }}/5.0</td>
                            </tr>
                            <tr>
                                <td class="col-metric"><strong>Score Range</strong></td>
                                <td class="col-value">{{ $overviewStats['scoreRange'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Score Distribution -->
            <div class="section page-break">
                <h2>Score Distribution</h2>
                <div class="section-content">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-level">Performance Level</th>
                                <th class="col-range">Score Range</th>
                                <th class="col-count">Number of Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="col-level"><strong>Excellent</strong></td>
                                <td class="col-range">90-100%</td>
                                <td class="col-count">{{ $assessmentAnalytics['scoreDistribution']['excellent'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-level"><strong>Good</strong></td>
                                <td class="col-range">80-89%</td>
                                <td class="col-count">{{ $assessmentAnalytics['scoreDistribution']['good'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-level"><strong>Average</strong></td>
                                <td class="col-range">70-79%</td>
                                <td class="col-count">{{ $assessmentAnalytics['scoreDistribution']['average'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-level"><strong>Below Average</strong></td>
                                <td class="col-range">60-69%</td>
                                <td class="col-count">{{ $assessmentAnalytics['scoreDistribution']['below_average'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-level"><strong>Poor</strong></td>
                                <td class="col-range">&lt;60%</td>
                                <td class="col-count">{{ $assessmentAnalytics['scoreDistribution']['poor'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistical Analysis -->
            <div class="section page-break">
                <h2>Statistical Analysis</h2>
                <div class="section-content">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-metric">Statistical Measure</th>
                                <th class="col-value">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="col-metric"><strong>Median Score</strong></td>
                                <td class="col-value">{{ $assessmentAnalytics['medianScore'] }}%</td>
                            </tr>
                            <tr>
                                <td class="col-metric"><strong>Standard Deviation</strong></td>
                                <td class="col-value">{{ $assessmentAnalytics['standardDeviation'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer">
                <p>This report was generated automatically by the InternConnect System</p>
            </div>
        </div>
    </div>
</body>
</html>