<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Placements Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #333; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        .stats { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .stats h3 { margin-top: 0; color: #333; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .stat-item { text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #059669; }
        .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: auto; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; font-size: 10px; }
        .col-student { width: 12%; }
        .col-name { width: 18%; }
        .col-section { width: 12%; }
        .col-company { width: 20%; }
        .col-position { width: 16%; }
        .col-department { width: 12%; }
        .col-score { width: 8%; }
        .col-status { width: 10%; }
        .col-date { width: 12%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>All Placements Report</h1>
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
                <div class="stat-value">{{ $stats['placedStudents'] }}</div>
                <div class="stat-label">Placed Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['placementRate'] }}%</div>
                <div class="stat-label">Placement Rate</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['totalSlots'] }}</div>
                <div class="stat-label">Total Slots</div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($allPlacements) && count($allPlacements) > 0)
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
            @foreach($allPlacements as $placement)
            <tr>
                <td class="col-student">{{ $placement['student_number'] }}</td>
                <td class="col-name">{{ $placement['name'] }}</td>
                <td class="col-section">{{ $placement['section'] }}</td>
                <td class="col-company">{{ $placement['company'] }}</td>
                <td class="col-position">{{ $placement['position'] }}</td>
                <td class="col-department">{{ $placement['department'] }}</td>
                <td class="col-score">{{ $placement['compatibility_score'] }}</td>
                <td class="col-status">{{ ucfirst($placement['status']) }}</td>
                <td class="col-date">{{ $placement['placement_date'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No placements found in the system.</p>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
