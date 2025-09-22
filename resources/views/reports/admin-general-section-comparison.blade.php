<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Section Comparison Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #333; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        .stats { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .stats h3 { margin-top: 0; color: #333; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .stat-item { text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #2563eb; }
        .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: auto; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; font-size: 10px; }
        .col-section { width: 20%; }
        .col-students { width: 15%; }
        .col-assessments { width: 15%; }
        .col-placed { width: 15%; }
        .col-rate { width: 15%; }
        .col-score { width: 20%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Section Comparison Report</h1>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    @if(isset($stats))
    <div class="stats">
        <h3>System Overview</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">{{ $stats['totalStudents'] }}</div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['completedAssessments'] }}</div>
                <div class="stat-label">Completed Assessments</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['placedStudents'] }}</div>
                <div class="stat-label">Placed Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['completionRate'] }}%</div>
                <div class="stat-label">Overall Completion Rate</div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($sectionAnalytics) && count($sectionAnalytics) > 0)
    <h3>Section Performance Comparison</h3>
    <table>
        <thead>
            <tr>
                <th class="col-section">Section</th>
                <th class="col-students">Total Students</th>
                <th class="col-assessments">Completed Assessments</th>
                <th class="col-placed">Placed Students</th>
                <th class="col-rate">Completion Rate</th>
                <th class="col-rate">Placement Rate</th>
                <th class="col-score">Average Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sectionAnalytics as $section)
            <tr>
                <td class="col-section">{{ $section['section'] }}</td>
                <td class="col-students">{{ $section['totalStudents'] }}</td>
                <td class="col-assessments">{{ $section['completedAssessments'] }}</td>
                <td class="col-placed">{{ $section['placedStudents'] }}</td>
                <td class="col-rate">{{ $section['completionRate'] }}%</td>
                <td class="col-rate">{{ $section['placementRate'] }}%</td>
                <td class="col-score">{{ $section['avgScore'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No section data found in the system.</p>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
