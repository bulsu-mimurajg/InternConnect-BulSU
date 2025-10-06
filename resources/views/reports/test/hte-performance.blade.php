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

    @if(isset($data['htes']))
    <h2>HTE Performance Data</h2>
    <table>
        <thead>
            <tr>
                <th>Company Name</th>
                <th>Total Internships</th>
                <th>Total Slots</th>
                <th>Filled Slots</th>
                <th>Utilization Rate</th>
                <th>Avg Compatibility</th>
                <th>Response Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['htes'] as $hte)
            <tr>
                <td>{{ $hte['company_name'] ?? 'N/A' }}</td>
                <td>{{ $hte['total_internships'] ?? 0 }}</td>
                <td>{{ $hte['total_slots'] ?? 0 }}</td>
                <td>{{ $hte['filled_slots'] ?? 0 }}</td>
                <td>{{ ($hte['utilization_rate'] ?? 0) }}%</td>
                <td>{{ ($hte['avg_compatibility'] ?? 0) }}%</td>
                <td>{{ $hte['response_time'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($data['summary']))
    <h2>Summary</h2>
    <ul>
        @foreach($data['summary'] as $key => $value)
        <li><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
        @endforeach
    </ul>
    @endif

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('M d, Y H:i:s') }} | BULSU InternConnect Test Report</p>
    </div>
</body>
</html>
