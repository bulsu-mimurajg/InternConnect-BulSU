<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Compatibility Report - {{ $internship->position_title }}</title>
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
        
        .col-rank { width: 8%; }
        .col-name { width: 25%; }
        .col-section { width: 20%; }
        .col-score { width: 15%; }
        .col-status { width: 15%; }
        .col-analysis { width: 17%; }
        
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
        
        .score-excellent { color: #059669; font-weight: bold; }
        .score-good { color: #f59e0b; font-weight: bold; }
        .score-fair { color: #ef4444; font-weight: bold; }
        
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
                <h1>Student Compatibility Report</h1>
            </div>
            
            <div class="generation-info">
                <p>{{ $hte->company_name }} - {{ $internship->position_title }} | Department: {{ $internship->department }} | Generated on {{ $generatedAt }}</p>
            </div>

    <!-- Internship Overview -->
    <div class="section">
        <h2>Internship Overview</h2>
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $internship->position_title }}</div>
                    <div class="stat-label">Position Title</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $internship->department }}</div>
                    <div class="stat-label">Department</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $internship->slot_count }}</div>
                    <div class="stat-label">Available Slots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->count() }}</div>
                    <div class="stat-label">Total Candidates</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ round($placements->avg('compatibility_score'), 2) }}</div>
                    <div class="stat-label">Average Compatibility</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->where('compatibility_score', '>=', 80)->count() }}</div>
                    <div class="stat-label">High Compatibility (≥80)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compatibility Analysis -->
    <div class="section">
        <h2>Compatibility Analysis</h2>
        <div class="stats">
            <h3>Score Distribution</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value score-excellent">{{ $placements->where('compatibility_score', '>=', 80)->count() }}</div>
                    <div class="stat-label">Excellent (≥80)</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value score-good">{{ $placements->whereBetween('compatibility_score', [60, 79])->count() }}</div>
                    <div class="stat-label">Good (60-79)</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value score-fair">{{ $placements->where('compatibility_score', '<', 60)->count() }}</div>
                    <div class="stat-label">Fair (<60)</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->max('compatibility_score') }}</div>
                    <div class="stat-label">Highest Score</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->min('compatibility_score') }}</div>
                    <div class="stat-label">Lowest Score</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ round($placements->avg('compatibility_score'), 2) }}</div>
                    <div class="stat-label">Average Score</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Compatibility Rankings -->
    <div class="section page-break">
        <h2>Student Compatibility Rankings</h2>
        @if($placements && $placements->count() > 0)
        <table>
            <thead>
                <tr>
                    <th class="col-rank">Rank</th>
                    <th class="col-name">Student Name</th>
                    <th class="col-section">Section</th>
                    <th class="col-score">Compatibility Score</th>
                    <th class="col-status">Status</th>
                    <th class="col-analysis">Compatibility Level</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placements as $index => $placement)
                <tr>
                    <td class="col-rank">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $placement['student_name'] }}</td>
                    <td class="col-section">{{ $placement['section'] }}</td>
                    <td class="col-score {{ $placement['compatibility_score'] >= 80 ? 'score-excellent' : ($placement['compatibility_score'] >= 60 ? 'score-good' : 'score-fair') }}">
                        {{ $placement['compatibility_score'] }}
                    </td>
                    <td class="col-status">{{ ucfirst($placement['status']) }}</td>
                    <td class="col-analysis">
                        @if($placement['compatibility_score'] >= 80)
                            <span class="score-excellent">Excellent Match</span>
                        @elseif($placement['compatibility_score'] >= 60)
                            <span class="score-good">Good Match</span>
                        @else
                            <span class="score-fair">Fair Match</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No student compatibility data available for this internship.</p>
        @endif
    </div>

    <!-- Top Candidates -->
    @if($placements && $placements->count() > 0)
    <div class="section page-break">
        <h2>Top Compatibility Candidates</h2>
        <p>Students with the highest compatibility scores for this internship position:</p>
        
        <table>
            <thead>
                <tr>
                    <th class="col-rank">Rank</th>
                    <th class="col-name">Student Name</th>
                    <th class="col-section">Section</th>
                    <th class="col-score">Score</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placements->take(10) as $index => $placement)
                <tr>
                    <td class="col-rank">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $placement['student_name'] }}</td>
                    <td class="col-section">{{ $placement['section'] }}</td>
                    <td class="col-score score-excellent">{{ $placement['compatibility_score'] }}</td>
                    <td class="col-status">{{ ucfirst($placement['status']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Section Analysis -->
    <div class="section">
        <h2>Section Performance Analysis</h2>
        <table>
            <thead>
                <tr>
                    <th class="col-section">Section</th>
                    <th class="col-rank">Students</th>
                    <th class="col-score">Average Score</th>
                    <th class="col-score">Highest Score</th>
                    <th class="col-rank">Top Performers</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placements->groupBy('section') as $section => $students)
                <tr>
                    <td class="col-section">{{ $section }}</td>
                    <td class="col-rank">{{ $students->count() }}</td>
                    <td class="col-score">{{ round($students->avg('compatibility_score'), 2) }}</td>
                    <td class="col-score score-excellent">{{ $students->max('compatibility_score') }}</td>
                    <td class="col-rank">{{ $students->where('compatibility_score', '>=', 80)->count() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Compatibility Insights -->
    <div class="section">
        <h2>Compatibility Insights</h2>
        <div class="stats">
            <h3>Key Insights</h3>
            <ul style="margin: 0; padding-left: 20px;">
                <li><strong>Total Candidates:</strong> {{ $placements->count() }} students evaluated for this position</li>
                <li><strong>High Compatibility:</strong> {{ $placements->where('compatibility_score', '>=', 80)->count() }} students ({{ round(($placements->where('compatibility_score', '>=', 80)->count() / max($placements->count(), 1)) * 100, 1) }}%) have excellent compatibility scores</li>
                <li><strong>Score Range:</strong> {{ $placements->min('compatibility_score') }} - {{ $placements->max('compatibility_score') }} ({{ round($placements->max('compatibility_score') - $placements->min('compatibility_score'), 1) }} point spread)</li>
                <li><strong>Best Performing Section:</strong> {{ $placements->groupBy('section')->sortByDesc(fn($students) => $students->avg('compatibility_score'))->keys()->first() ?? 'N/A' }} with an average score of {{ round($placements->groupBy('section')->sortByDesc(fn($students) => $students->avg('compatibility_score'))->first()->avg('compatibility_score') ?? 0, 2) }}</li>
                <li><strong>Recommendation:</strong> 
                    @if($placements->where('compatibility_score', '>=', 80)->count() >= $internship->slot_count)
                        Excellent candidate pool with sufficient high-compatibility students for all available slots.
                    @elseif($placements->where('compatibility_score', '>=', 60)->count() >= $internship->slot_count)
                        Good candidate pool with adequate compatibility for filling all positions.
                    @else
                        Limited candidate pool; consider reviewing matching criteria or expanding search.
                    @endif
                </li>
            </ul>
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
