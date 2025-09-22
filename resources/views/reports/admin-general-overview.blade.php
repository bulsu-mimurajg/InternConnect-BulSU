<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>System Overview Report</title>
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
        <h1>System Overview Report</h1>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    @if(isset($stats))
    <div class="stats">
        <h3>System Statistics</h3>
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
                <div class="stat-value">{{ $stats['totalHTEs'] }}</div>
                <div class="stat-label">Total HTEs</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['activeHTEs'] }}</div>
                <div class="stat-label">Active HTEs</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['totalInternships'] }}</div>
                <div class="stat-label">Total Internships</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['totalSlots'] }}</div>
                <div class="stat-label">Total Slots</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['completionRate'] }}%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['placementRate'] }}%</div>
                <div class="stat-label">Placement Rate</div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($sectionStats) && count($sectionStats) > 0)
    <h3>Section Performance</h3>
    <table>
        <thead>
            <tr>
                <th>Section</th>
                <th>Total Students</th>
                <th>Completed Assessments</th>
                <th>Placed Students</th>
                <th>Completion Rate</th>
                <th>Placement Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sectionStats as $section)
            <tr>
                <td>{{ $section['section'] }}</td>
                <td>{{ $section['totalStudents'] }}</td>
                <td>{{ $section['completedAssessments'] }}</td>
                <td>{{ $section['placedStudents'] }}</td>
                <td>{{ $section['completionRate'] }}%</td>
                <td>{{ $section['placementRate'] }}%</td>
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
