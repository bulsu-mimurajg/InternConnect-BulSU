<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assessment Summary Report - {{ $sectionName }}</title>
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
        <h1>Assessment Summary Report</h1>
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

    <div class="section-title">Assessment Statistics</div>
    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Students</td>
                <td>{{ $overviewStats['totalStudents'] }}</td>
            </tr>
            <tr>
                <td>Completed Assessments</td>
                <td>{{ $overviewStats['completedAssessments'] }}</td>
            </tr>
            <tr>
                <td>Pending Students</td>
                <td>{{ $overviewStats['pendingStudents'] }}</td>
            </tr>
            <tr>
                <td>Completion Rate</td>
                <td>{{ $overviewStats['completionRate'] }}%</td>
            </tr>
            <tr>
                <td>Average Score</td>
                <td>{{ $overviewStats['averageScore'] }}/5.0</td>
            </tr>
            <tr>
                <td>Highest Score</td>
                <td>{{ $overviewStats['highestScore'] }}/5.0</td>
            </tr>
            <tr>
                <td>Lowest Score</td>
                <td>{{ $overviewStats['lowestScore'] }}/5.0</td>
            </tr>
            <tr>
                <td>Score Range</td>
                <td>{{ $overviewStats['scoreRange'] }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Score Distribution</div>
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

    <div class="section-title">Statistical Analysis</div>
    <table>
        <thead>
            <tr>
                <th>Statistical Measure</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Median Score</td>
                <td>{{ $assessmentAnalytics['medianScore'] }}%</td>
            </tr>
            <tr>
                <td>Standard Deviation</td>
                <td>{{ $assessmentAnalytics['standardDeviation'] }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
