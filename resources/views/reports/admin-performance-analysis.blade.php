<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Analysis Report - {{ $sectionName }}</title>
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
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Performance Analysis Report</h1>
        <p>Section: {{ $sectionName }}</p>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    @if(isset($overviewStats))
    <div class="stats">
        <h3>Overview Statistics</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">{{ $overviewStats['totalStudents'] }}</div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $overviewStats['completedAssessments'] }}</div>
                <div class="stat-label">Completed Assessments</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $overviewStats['placedStudents'] }}</div>
                <div class="stat-label">Placed Students</div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($performanceData) && isset($performanceData['topPerformers']) && count($performanceData['topPerformers']) > 0)
    <h3>Top Performers</h3>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Name</th>
                <th>Student Number</th>
                <th>Score</th>
                <th>Percentage</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($performanceData['topPerformers'] as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student['name'] }}</td>
                <td>{{ $student['student_number'] }}</td>
                <td>{{ $student['score'] }}</td>
                <td>{{ $student['percentage'] }}%</td>
                <td>{{ $student['submittedAt'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
