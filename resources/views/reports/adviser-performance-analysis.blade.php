<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Analysis Report - {{ $sectionName }}</title>
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
        .section-title {
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #333;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Performance Analysis Report</h1>
        <p>Section: {{ $sectionName }}</p>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    <div class="section-title">Category Performance Analysis</div>
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

    <div class="section-title">Top Performers</div>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Name</th>
                <th>Username</th>
                @if($sectionName === 'All Sections')
                <th>Section</th>
                @endif
                <th>Score</th>
                <th>Percentage</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topPerformers as $student)
                @if($student['hasAssessment'])
                <tr>
                    <td>{{ $student['rank'] }}</td>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['username'] }}</td>
                    @if($sectionName === 'All Sections')
                    <td>{{ $student['section'] ?? 'N/A' }}</td>
                    @endif
                    <td>{{ $student['score'] }}</td>
                    <td>{{ $student['percentage'] }}%</td>
                    <td>{{ $student['submittedAt'] ?? 'N/A' }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Score Distribution Analysis</div>
    <table>
        <thead>
            <tr>
                <th>Performance Level</th>
                <th>Score Range</th>
                <th>Number of Students</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Excellent</td>
                <td>90-100%</td>
                <td>{{ $assessmentAnalytics['scoreDistribution']['excellent'] }}</td>
            </tr>
            <tr>
                <td>Good</td>
                <td>80-89%</td>
                <td>{{ $assessmentAnalytics['scoreDistribution']['good'] }}</td>
            </tr>
            <tr>
                <td>Average</td>
                <td>70-79%</td>
                <td>{{ $assessmentAnalytics['scoreDistribution']['average'] }}</td>
            </tr>
            <tr>
                <td>Below Average</td>
                <td>60-69%</td>
                <td>{{ $assessmentAnalytics['scoreDistribution']['below_average'] }}</td>
            </tr>
            <tr>
                <td>Poor</td>
                <td>&lt;60%</td>
                <td>{{ $assessmentAnalytics['scoreDistribution']['poor'] }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
