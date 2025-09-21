<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student List Report - {{ $sectionName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .category-section {
            margin-bottom: 20px;
        }
        .category-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student List Report</h1>
        <p>Section: {{ $sectionName }}</p>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-value">{{ $overviewStats['totalStudents'] }}</div>
            <div class="stat-label">Total Students</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $overviewStats['completedAssessments'] }}</div>
            <div class="stat-label">Completed Assessments</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $overviewStats['completionRate'] }}%</div>
            <div class="stat-label">Completion Rate</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $overviewStats['averageScore'] }}</div>
            <div class="stat-label">Average Score</div>
        </div>
    </div>

    <h2>Student Information</h2>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Status</th>
                <th>Assessment</th>
                <th>Score</th>
                <th>Percentage</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentProgress as $student)
            <tr>
                <td>{{ $student['rank'] }}</td>
                <td>{{ $student['name'] }}</td>
                <td>{{ $student['username'] }}</td>
                <td>{{ $student['email'] ?? 'N/A' }}</td>
                <td>{{ ucfirst($student['status']) }}</td>
                <td>{{ $student['hasAssessment'] ? 'Completed' : 'Pending' }}</td>
                <td>{{ $student['hasAssessment'] ? $student['score'] : 'N/A' }}</td>
                <td>{{ $student['hasAssessment'] ? $student['percentage'] . '%' : 'N/A' }}</td>
                <td>{{ $student['submittedAt'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($categoryBreakdown))
    <h2>Category Performance</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Average Score</th>
                <th>Total Questions</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryBreakdown as $category)
            <tr>
                <td>{{ $category['category'] }}</td>
                <td>{{ $category['averageScore'] }}/5.0</td>
                <td>{{ $category['totalQuestions'] }}</td>
                <td>{{ $category['percentage'] }}%</td>
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
