<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Assessment Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: #2c3e50;
            background-color: #ffffff;
            line-height: 1.6;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
        }
        .content-wrapper {
            max-width: 800px !important;
            margin: 0 auto !important;
            width: 100% !important;
        }
        .content-wrapper * {
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .content-wrapper .section,
        .content-wrapper .stats,
        .content-wrapper table,
        .content-wrapper .metric-group {
            max-width: 100% !important;
            width: 100% !important;
        }

        .document-header {
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 3px solid #e67e22;
            padding-bottom: 20px;
        }
        .document-header img {
            max-width: 100%;
            height: auto;
            max-height: 180px;
        }

        .header {
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: bold;
        }
        .header .subtitle {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .header .date {
            color: #95a5a6;
            font-size: 14px;
        }

        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            color: #2c3e50;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #e67e22;
            padding-bottom: 5px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #e67e22;
            text-align: center;
        }
        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        th {
            background-color: #e67e22;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e8f4f8;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #7f8c8d;
            font-size: 12px;
            border-top: 1px solid #ecf0f1;
            padding-top: 20px;
        }

        @media print {
            body { margin: 0; padding: 15px; }
            .section { page-break-inside: avoid; }
            .stats { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-wrapper">
            <!-- Document Header -->
            <div class="document-header">
                <img src="{{ public_path('images/document_header.png') }}" alt="BulSU Document Header" style="max-width: 100%; height: auto;">
            </div>

            <!-- Report Header -->
            <div class="header">
                <h1>Student Assessment Report</h1>
                <div class="subtitle">{{ $section_name }}</div>
                <div class="date">Generated on {{ now()->format('F d, Y \a\t g:i A') }}</div>
            </div>

            <!-- Statistics Overview -->
            <div class="section">
                <h2 class="section-title">Statistics Overview</h2>
                <div class="stats">
                    @foreach($stats as $stat)
                        <div class="stat-card">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Student Assessment List -->
            <div class="section">
                <h2 class="section-title">Student Assessments</h2>
                @if($students->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student Number</th>
                                <th>Section</th>
                                <th>Total Score</th>
                                <th>Assessment Status</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                <tr>
                                    <td>
                                        {{ $student->last_name }}, {{ $student->first_name }}
                                        @if($student->middle_name)
                                            {{ strtoupper(substr($student->middle_name, 0, 1)) }}.
                                        @endif
                                    </td>
                                    <td>{{ $student->student_number }}</td>
                                    <td>{{ $student->section->section_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($student->scores && $student->scores->count() > 0)
                                            <span style="color: {{ $student->scores->sum('score') >= 80 ? '#27ae60' : ($student->scores->sum('score') >= 60 ? '#f39c12' : '#e74c3c') }}; font-weight: bold;">
                                                {{ $student->scores->sum('score') }}
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <span style="color: {{ $student->is_submit ? '#27ae60' : '#e74c3c' }}; font-weight: bold;">
                                            {{ $student->is_submit ? 'Submitted' : 'Not Submitted' }}
                                        </span>
                                    </td>
                                    <td>{{ $student->user->email ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align: center; color: #7f8c8d; font-style: italic; padding: 20px;">
                        No students found for the selected criteria.
                    </p>
                @endif
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>This report was generated by the InternConnect BSIT system.</p>
                <p>For questions or concerns, please contact the system administrator.</p>
            </div>
        </div>
    </div>
</body>
</html>
