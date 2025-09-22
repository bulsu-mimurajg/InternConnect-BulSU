<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Students Report</title>
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
        <h1>All Students Report</h1>
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
                <div class="stat-value">{{ $stats['completionRate'] }}%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($allStudents) && count($allStudents) > 0)
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Section</th>
                <th>Has Assessment</th>
                <th>Assessment Score</th>
                <th>Assessment %</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allStudents as $student)
            <tr>
                <td>{{ $student['username'] }}</td>
                <td>{{ $student['name'] }}</td>
                <td>{{ $student['email'] }}</td>
                <td>{{ ucfirst($student['status']) }}</td>
                <td>{{ $student['section'] }}</td>
                <td>{{ $student['hasAssessment'] ? 'Yes' : 'No' }}</td>
                <td>{{ $student['assessmentScore'] }}</td>
                <td>{{ $student['assessmentPercentage'] }}%</td>
                <td>{{ $student['submittedAt'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No students found in the system.</p>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
