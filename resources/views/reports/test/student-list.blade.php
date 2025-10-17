<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Report - {{ $reportConfig['name'] }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #e67e22;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
        }
        .test-badge {
            display: inline-block;
            background-color: #e67e22;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .note {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $reportConfig['name'] }}</h1>
        <span class="test-badge">TEST DATA</span>
    </div>

    <div class="note">
        <strong>Test Report Notice:</strong> This report contains generated test data for demonstration purposes. 
        Generated on {{ $generatedAt->format('M d, Y H:i:s') }}.
    </div>

    @if(isset($data['students']))
    <h2>Student List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student Number</th>
                <th>Name</th>
                <th>Section</th>
                <th>Specialization</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['students'] as $student)
            <tr>
                <td>{{ $student['id'] ?? 'N/A' }}</td>
                <td>{{ $student['student_number'] ?? 'N/A' }}</td>
                <td>{{ ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '') }}</td>
                <td>{{ $student['section'] ?? 'N/A' }}</td>
                <td>{{ $student['specialization'] ?? 'N/A' }}</td>
                <td>{{ ($student['is_active'] ?? false) ? 'Active' : 'Inactive' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($data['section']))
    <p><strong>Section:</strong> {{ $data['section'] }}</p>
    @endif

    @if(isset($data['total_count']))
    <p><strong>Total Count:</strong> {{ $data['total_count'] }}</p>
    @endif

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('M d, Y H:i:s') }} | BULSU InternConnect Test Report</p>
    </div>
</body>
</html>
