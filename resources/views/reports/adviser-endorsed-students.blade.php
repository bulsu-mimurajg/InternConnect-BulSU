<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Endorsed Students Report - {{ $sectionName }}</title>
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
        <h1>Endorsed Students Report</h1>
        <p>Section: {{ $sectionName }}</p>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-value">{{ $overviewStats['totalStudents'] }}</div>
            <div class="stat-label">Total Students</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ count($endorsedStudents) }}</div>
            <div class="stat-label">Endorsed Students</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $overviewStats['totalStudents'] > 0 ? round((count($endorsedStudents) / $overviewStats['totalStudents']) * 100, 1) : 0 }}%</div>
            <div class="stat-label">Endorsement Rate</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ count($endorsedStudents) > 0 ? round(collect($endorsedStudents)->avg('compatibility_score'), 1) : 0 }}</div>
            <div class="stat-label">Avg Compatibility</div>
        </div>
    </div>

    <div class="section-title">Endorsed Students Details</div>
    <table>
        <thead>
            <tr>
                <th>Student Number</th>
                <th>Name</th>
                <th>Section</th>
                <th>Company</th>
                <th>Position</th>
                <th>Department</th>
                <th>Compatibility Score</th>
                <th>Status</th>
                <th>Endorsement Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($endorsedStudents as $endorsement)
            <tr>
                <td>{{ $endorsement['student']['student_number'] }}</td>
                <td>{{ $endorsement['student']['first_name'] }} {{ $endorsement['student']['last_name'] }}</td>
                <td>{{ $endorsement['student']['section'] }}</td>
                <td>{{ $endorsement['internship']['hte']['company_name'] }}</td>
                <td>{{ $endorsement['internship']['position_title'] }}</td>
                <td>{{ $endorsement['internship']['department'] }}</td>
                <td>{{ $endorsement['compatibility_score'] }}%</td>
                <td>{{ ucfirst($endorsement['status']) }}</td>
                <td>{{ $endorsement['endorsement_date'] ? $endorsement['endorsement_date']->format('M d, Y') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(empty($endorsedStudents))
    <div class="section-title">No Endorsed Students</div>
    <p>There are currently no endorsed students in this section.</p>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
