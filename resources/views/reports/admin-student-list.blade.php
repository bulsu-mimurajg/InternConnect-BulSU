<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student List Report - {{ $sectionName }}</title>
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
        .col-username { width: 12%; }
        .col-email { width: 18%; }
        .col-name { width: 15%; }
        .col-status { width: 10%; }
        .col-section { width: 12%; }
        .col-assessment { width: 10%; }
        .col-score { width: 10%; }
        .col-percentage { width: 10%; }
        .col-date { width: 13%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student List Report</h1>
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
            <div class="stat-item">
                <div class="stat-value">{{ $overviewStats['completionRate'] }}%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($students) && count($students) > 0)
    <table>
        <thead>
            <tr>
                <th class="col-username">Username</th>
                <th class="col-name">Name</th>
                <th class="col-email">Email</th>
                <th class="col-status">Status</th>
                <th class="col-assessment">Has Assessment</th>
                <th class="col-score">Assessment Score</th>
                <th class="col-percentage">Assessment %</th>
                <th class="col-date">Submitted At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td class="col-username">{{ $student['username'] }}</td>
                <td class="col-name">{{ $student['name'] }}</td>
                <td class="col-email">{{ $student['email'] }}</td>
                <td class="col-status">{{ ucfirst($student['status']) }}</td>
                <td class="col-assessment">{{ $student['hasAssessment'] ? 'Yes' : 'No' }}</td>
                <td class="col-score">{{ $student['assessmentScore'] }}</td>
                <td class="col-percentage">{{ $student['assessmentPercentage'] }}%</td>
                <td class="col-date">{{ $student['submittedAt'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No students found for this section.</p>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
