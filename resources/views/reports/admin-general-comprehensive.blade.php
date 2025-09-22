<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive System Report</title>
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
        .col-name { width: 18%; }
        .col-section { width: 12%; }
        .col-company { width: 20%; }
        .col-position { width: 16%; }
        .col-department { width: 12%; }
        .col-score { width: 8%; }
        .col-status { width: 10%; }
        .col-date { width: 12%; }
        .col-internships { width: 10%; }
        .col-slots { width: 10%; }
        .col-rate { width: 12%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Comprehensive System Report</h1>
        <p>Generated on {{ $generatedAt }}</p>
    </div>

    <!-- System Overview -->
    <div class="section">
        <h2>System Overview</h2>
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['totalStudents'] }}</div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['completedAssessments'] }}</div>
                    <div class="stat-label">Completed Assessments</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['placedStudents'] }}</div>
                    <div class="stat-label">Placed Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['totalHTEs'] }}</div>
                    <div class="stat-label">Total HTEs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['activeHTEs'] }}</div>
                    <div class="stat-label">Active HTEs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['totalInternships'] }}</div>
                    <div class="stat-label">Total Internships</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['totalSlots'] }}</div>
                    <div class="stat-label">Total Slots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['completionRate'] }}%</div>
                    <div class="stat-label">Completion Rate</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['placementRate'] }}%</div>
                    <div class="stat-label">Placement Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Performance -->
    <div class="section page-break">
        <h2>Section Performance Analysis</h2>
        @if(isset($sectionAnalytics) && count($sectionAnalytics) > 0)
        <table>
            <thead>
                <tr>
                    <th class="col-section">Section</th>
                    <th class="col-student">Total Students</th>
                    <th class="col-student">Completed Assessments</th>
                    <th class="col-student">Placed Students</th>
                    <th class="col-rate">Completion Rate</th>
                    <th class="col-rate">Placement Rate</th>
                    <th class="col-score">Average Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sectionAnalytics as $section)
                <tr>
                    <td class="col-section">{{ $section['section'] }}</td>
                    <td class="col-student">{{ $section['totalStudents'] }}</td>
                    <td class="col-student">{{ $section['completedAssessments'] }}</td>
                    <td class="col-student">{{ $section['placedStudents'] }}</td>
                    <td class="col-rate">{{ $section['completionRate'] }}%</td>
                    <td class="col-rate">{{ $section['placementRate'] }}%</td>
                    <td class="col-score">{{ $section['avgScore'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No section data available.</p>
        @endif
    </div>

    <!-- HTE Performance -->
    <div class="section page-break">
        <h2>HTE Performance Overview</h2>
        @if(isset($hteStats) && count($hteStats) > 0)
        <table>
            <thead>
                <tr>
                    <th class="col-company">Company</th>
                    <th class="col-name">Contact Person</th>
                    <th class="col-status">Status</th>
                    <th class="col-internships">Total Internships</th>
                    <th class="col-internships">Active Internships</th>
                    <th class="col-slots">Total Slots</th>
                    <th class="col-slots">Filled Slots</th>
                    <th class="col-rate">Utilization Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hteStats as $hte)
                <tr>
                    <td class="col-company">{{ $hte['company_name'] }}</td>
                    <td class="col-name">{{ $hte['contact_person'] }}</td>
                    <td class="col-status">{{ $hte['is_submit'] ? 'Active' : 'Inactive' }}</td>
                    <td class="col-internships">{{ $hte['totalInternships'] }}</td>
                    <td class="col-internships">{{ $hte['activeInternships'] }}</td>
                    <td class="col-slots">{{ $hte['totalSlots'] }}</td>
                    <td class="col-slots">{{ $hte['filledSlots'] }}</td>
                    <td class="col-rate">{{ $hte['utilizationRate'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No HTE data available.</p>
        @endif
    </div>

    <!-- All Placements -->
    <div class="section page-break">
        <h2>All Placements Summary</h2>
        @if(isset($allPlacements) && count($allPlacements) > 0)
        <div class="stats">
            <h3>Placement Statistics</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ count($allPlacements) }}</div>
                    <div class="stat-label">Total Placements</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ count(array_filter($allPlacements, fn($p) => $p['status'] === 'approved')) }}</div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ count(array_filter($allPlacements, fn($p) => $p['status'] === 'pending')) }}</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ count(array_filter($allPlacements, fn($p) => $p['status'] === 'rejected')) }}</div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th class="col-student">Student Number</th>
                    <th class="col-name">Name</th>
                    <th class="col-section">Section</th>
                    <th class="col-company">Company</th>
                    <th class="col-position">Position</th>
                    <th class="col-department">Department</th>
                    <th class="col-score">Score</th>
                    <th class="col-status">Status</th>
                    <th class="col-date">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allPlacements as $placement)
                <tr>
                    <td class="col-student">{{ $placement['student_number'] }}</td>
                    <td class="col-name">{{ $placement['name'] }}</td>
                    <td class="col-section">{{ $placement['section'] }}</td>
                    <td class="col-company">{{ $placement['company'] }}</td>
                    <td class="col-position">{{ $placement['position'] }}</td>
                    <td class="col-department">{{ $placement['department'] }}</td>
                    <td class="col-score">{{ $placement['compatibility_score'] }}</td>
                    <td class="col-status">{{ ucfirst($placement['status']) }}</td>
                    <td class="col-date">{{ $placement['placement_date'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No placements found in the system.</p>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
