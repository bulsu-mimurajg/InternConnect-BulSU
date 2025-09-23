<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placed Students Report - {{ $hte->company_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #059669; padding-bottom: 20px; }
        .header h1 { color: #059669; margin: 0; font-size: 28px; }
        .header p { margin: 5px 0 0 0; color: #666; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #059669; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .stats { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .stats h3 { margin-top: 0; color: #333; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .stat-item { text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #059669; }
        .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: auto; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; font-size: 10px; }
        .col-student { width: 12%; }
        .col-name { width: 20%; }
        .col-section { width: 15%; }
        .col-position { width: 25%; }
        .col-department { width: 15%; }
        .col-score { width: 10%; }
        .col-date { width: 13%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Placed Students Report</h1>
        <p>{{ $hte->company_name }}</p>
        <p>Generated on {{ $generatedAt }}</p>
    </div>

    <!-- Summary Statistics -->
    <div class="section">
        <h2>Summary Statistics</h2>
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $placedStudents->count() }}</div>
                    <div class="stat-label">Total Placed Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placedStudents->groupBy('section')->count() }}</div>
                    <div class="stat-label">Sections Represented</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placedStudents->groupBy('position')->count() }}</div>
                    <div class="stat-label">Different Positions</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ round($placedStudents->avg('compatibility_score'), 2) }}</div>
                    <div class="stat-label">Average Compatibility Score</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Placed Students List -->
    <div class="section page-break">
        <h2>Placed Students Details</h2>
        @if($placedStudents && $placedStudents->count() > 0)
        <table>
            <thead>
                <tr>
                    <th class="col-student">Student Number</th>
                    <th class="col-name">Name</th>
                    <th class="col-section">Section</th>
                    <th class="col-position">Position</th>
                    <th class="col-department">Department</th>
                    <th class="col-score">Compatibility Score</th>
                    <th class="col-date">Placement Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placedStudents as $student)
                <tr>
                    <td class="col-student">{{ $student['student_number'] }}</td>
                    <td class="col-name">{{ $student['name'] }}</td>
                    <td class="col-section">{{ $student['section'] }}</td>
                    <td class="col-position">{{ $student['position'] }}</td>
                    <td class="col-department">{{ $student['department'] }}</td>
                    <td class="col-score">{{ $student['compatibility_score'] }}</td>
                    <td class="col-date">{{ $student['placement_date'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No students have been placed in your internships yet.</p>
        @endif
    </div>

    <!-- Section Breakdown -->
    @if($placedStudents && $placedStudents->count() > 0)
    <div class="section page-break">
        <h2>Students by Section</h2>
        <table>
            <thead>
                <tr>
                    <th class="col-section">Section</th>
                    <th class="col-student">Number of Students</th>
                    <th class="col-score">Average Score</th>
                    <th class="col-position">Positions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placedStudents->groupBy('section') as $section => $students)
                <tr>
                    <td class="col-section">{{ $section }}</td>
                    <td class="col-student">{{ $students->count() }}</td>
                    <td class="col-score">{{ round($students->avg('compatibility_score'), 2) }}</td>
                    <td class="col-position">{{ $students->pluck('position')->unique()->implode(', ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
