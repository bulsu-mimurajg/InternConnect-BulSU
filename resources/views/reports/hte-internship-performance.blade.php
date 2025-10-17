<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Performance Report - {{ $internship->position_title }}</title>
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
        .col-name { width: 25%; }
        .col-section { width: 20%; }
        .col-score { width: 15%; }
        .col-status { width: 15%; }
        .col-date { width: 25%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Internship Performance Report</h1>
        <p>{{ $hte->company_name }} - {{ $internship->position_title }}</p>
        <p>Department: {{ $internship->department }}</p>
        <p>Generated on {{ $generatedAt }}</p>
    </div>

    <!-- Internship Overview -->
    <div class="section">
        <h2>Internship Overview</h2>
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $internship->position_title }}</div>
                    <div class="stat-label">Position Title</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $internship->department }}</div>
                    <div class="stat-label">Department</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $internship->slot_count }}</div>
                    <div class="stat-label">Total Slots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $filledSlots }}</div>
                    <div class="stat-label">Filled Slots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $utilizationRate }}%</div>
                    <div class="stat-label">Utilization Rate</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $avgCompatibilityScore }}</div>
                    <div class="stat-label">Average Compatibility Score</div>
                </div>
            </div>
        </div>
        <p><strong>Description:</strong> {{ $internship->placement_description }}</p>
        <p><strong>Status:</strong> {{ $internship->is_active ? 'Active' : 'Inactive' }}</p>
        <p><strong>Created:</strong> {{ $internship->created_at->format('F d, Y') }}</p>
    </div>

    <!-- Performance Summary -->
    <div class="section">
        <h2>Performance Summary</h2>
        <div class="stats">
            <h3>Key Metrics</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->count() }}</div>
                    <div class="stat-label">Total Placements</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->where('status', 'approved')->count() }}</div>
                    <div class="stat-label">Approved Placements</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->where('status', 'pending')->count() }}</div>
                    <div class="stat-label">Pending Placements</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->where('status', 'rejected')->count() }}</div>
                    <div class="stat-label">Rejected Placements</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Placements -->
    <div class="section page-break">
        <h2>Student Placements</h2>
        @if($placements && $placements->count() > 0)
        <table>
            <thead>
                <tr>
                    <th class="col-name">Student Name</th>
                    <th class="col-section">Section</th>
                    <th class="col-score">Compatibility Score</th>
                    <th class="col-status">Status</th>
                    <th class="col-date">Placement Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placements as $placement)
                <tr>
                    <td class="col-name">{{ $placement['student_name'] }}</td>
                    <td class="col-section">{{ $placement['section'] }}</td>
                    <td class="col-score">{{ $placement['compatibility_score'] }}</td>
                    <td class="col-status">{{ ucfirst($placement['status']) }}</td>
                    <td class="col-date">{{ $placement['placement_date'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No students have been placed in this internship yet.</p>
        @endif
    </div>

    <!-- Section Analysis -->
    @if($placements && $placements->count() > 0)
    <div class="section page-break">
        <h2>Section Analysis</h2>
        <table>
            <thead>
                <tr>
                    <th class="col-section">Section</th>
                    <th class="col-score">Number of Students</th>
                    <th class="col-score">Average Score</th>
                    <th class="col-status">Approved</th>
                    <th class="col-status">Pending</th>
                    <th class="col-status">Rejected</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placements->groupBy('section') as $section => $students)
                <tr>
                    <td class="col-section">{{ $section }}</td>
                    <td class="col-score">{{ $students->count() }}</td>
                    <td class="col-score">{{ round($students->avg('compatibility_score'), 2) }}</td>
                    <td class="col-status">{{ $students->where('status', 'approved')->count() }}</td>
                    <td class="col-status">{{ $students->where('status', 'pending')->count() }}</td>
                    <td class="col-status">{{ $students->where('status', 'rejected')->count() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Score Distribution -->
    <div class="section">
        <h2>Compatibility Score Distribution</h2>
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->max('compatibility_score') }}</div>
                    <div class="stat-label">Highest Score</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->min('compatibility_score') }}</div>
                    <div class="stat-label">Lowest Score</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ round($placements->avg('compatibility_score'), 2) }}</div>
                    <div class="stat-label">Average Score</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->where('compatibility_score', '>=', 80)->count() }}</div>
                    <div class="stat-label">High Scores (≥80)</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
