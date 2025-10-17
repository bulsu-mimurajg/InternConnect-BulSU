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
        .header .subtitle {
            margin: 0;
            font-size: 16px;
            color: #7f8c8d;
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
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            margin: 0 0 15px 0;
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #e67e22;
            padding-bottom: 5px;
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
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #e67e22;
            text-align: center;
        }
        .summary-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #e67e22;
            margin-bottom: 5px;
        }
        .summary-card .label {
            font-size: 14px;
            color: #7f8c8d;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
        .note {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin-bottom: 20px;
        }
        .note strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $reportConfig['name'] }}</h1>
        <p class="subtitle">{{ $reportConfig['description'] }}</p>
        <span class="test-badge">TEST DATA</span>
    </div>

    <div class="note">
        <strong>Test Report Notice:</strong> This report contains generated test data for demonstration purposes. 
        Generated on {{ $generatedAt->format('M d, Y H:i:s') }}.
    </div>

    @if(isset($data['summary']))
    <div class="section">
        <h2>Summary</h2>
        <div class="summary-grid">
            @foreach($data['summary'] as $key => $value)
            <div class="summary-card">
                <div class="number">{{ is_numeric($value) ? number_format($value) : $value }}</div>
                <div class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(isset($data['students']))
    <div class="section">
        <h2>Students</h2>
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
    </div>
    @endif

    @if(isset($data['htes']))
    <div class="section">
        <h2>Host Training Establishments</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Utilization Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['htes'] as $hte)
                <tr>
                    <td>{{ $hte['id'] ?? 'N/A' }}</td>
                    <td>{{ $hte['company_name'] ?? 'N/A' }}</td>
                    <td>{{ $hte['contact_person'] ?? 'N/A' }}</td>
                    <td>{{ $hte['email'] ?? 'N/A' }}</td>
                    <td>{{ $hte['phone'] ?? 'N/A' }}</td>
                    <td>{{ ($hte['utilization_rate'] ?? 0) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['placed_students']))
    <div class="section">
        <h2>Placed Students</h2>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Company</th>
                    <th>Position</th>
                    <th>Compatibility Score</th>
                    <th>Placement Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['placed_students'] as $placement)
                <tr>
                    <td>{{ ($placement['first_name'] ?? '') . ' ' . ($placement['last_name'] ?? '') }}</td>
                    <td>{{ $placement['company_name'] ?? 'N/A' }}</td>
                    <td>{{ $placement['position_title'] ?? 'N/A' }}</td>
                    <td>{{ ($placement['compatibility_score'] ?? 0) }}%</td>
                    <td>{{ isset($placement['placement_date']) ? \Carbon\Carbon::parse($placement['placement_date'])->format('M d, Y') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['internships']))
    <div class="section">
        <h2>Internships</h2>
        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Slots</th>
                    <th>Filled</th>
                    <th>Duration</th>
                    <th>Start Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['internships'] as $internship)
                <tr>
                    <td>{{ $internship['position_title'] ?? 'N/A' }}</td>
                    <td>{{ $internship['slot_count'] ?? 0 }}</td>
                    <td>{{ $internship['filled_slots'] ?? 0 }}</td>
                    <td>{{ $internship['duration'] ?? 'N/A' }}</td>
                    <td>{{ $internship['start_date'] ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('M d, Y H:i:s') }} | BULSU InternConnect Test Report</p>
        <p>This is a test report with generated data for demonstration purposes.</p>
    </div>
</body>
</html>
