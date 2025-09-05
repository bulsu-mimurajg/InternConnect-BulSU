<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Report - {{ $generatedAt }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #2563eb;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Comprehensive Internship Management Report</h1>
        <p>Generated on {{ $generatedAt }}</p>
    </div>

    <div class="section">
        <h2>Key Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['totalStudents'] }}</div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['completedAssessments'] }}</div>
                <div class="stat-label">Completed Assessments</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['placedStudents'] }}</div>
                <div class="stat-label">Placed Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['activeHTEs'] }}</div>
                <div class="stat-label">Active HTEs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['totalSlots'] }}</div>
                <div class="stat-label">Available Slots</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['completionRate'] }}%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['placementRate'] }}%</div>
                <div class="stat-label">Placement Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['hteParticipationRate'] }}%</div>
                <div class="stat-label">HTE Participation Rate</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    @if(isset($chartImages))
    <div class="section">
        <h2>Visual Analytics</h2>
        <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin: 20px 0;">
            
            <!-- Assessment Status Chart -->
            @if(isset($chartImages['assessment_status']))
            <div class="chart-container" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; background: white;">
                <h3 style="text-align: center; margin-bottom: 20px; color: #2563eb;">Assessment Completion Status</h3>
                <div style="text-align: center;">
                    {!! file_get_contents($chartImages['assessment_status']) !!}
                </div>
            </div>
            @endif

            <!-- Placement Status Chart -->
            @if(isset($chartImages['placement_status']))
            <div class="chart-container" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; background: white;">
                <h3 style="text-align: center; margin-bottom: 20px; color: #2563eb;">Placement Status Distribution</h3>
                <div style="text-align: center;">
                    {!! file_get_contents($chartImages['placement_status']) !!}
                </div>
            </div>
            @endif

            <!-- Category Scores Chart -->
            @if(isset($chartImages['category_scores']))
            <div class="chart-container" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; background: white; grid-column: 1 / -1;">
                <h3 style="text-align: center; margin-bottom: 20px; color: #2563eb;">Assessment Scores by Category</h3>
                <div style="text-align: center;">
                    {!! file_get_contents($chartImages['category_scores']) !!}
                </div>
            </div>
            @endif

            <!-- Company Placements Chart -->
            @if(isset($chartImages['company_placements']))
            <div class="chart-container" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; background: white; grid-column: 1 / -1;">
                <h3 style="text-align: center; margin-bottom: 20px; color: #2563eb;">Company Placement Performance</h3>
                <div style="text-align: center;">
                    {!! file_get_contents($chartImages['company_placements']) !!}
                </div>
            </div>
            @endif

            <!-- Section Performance Chart -->
            @if(isset($chartImages['section_performance']))
            <div class="chart-container" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; background: white; grid-column: 1 / -1;">
                <h3 style="text-align: center; margin-bottom: 20px; color: #2563eb;">Section Performance Comparison</h3>
                <div style="text-align: center;">
                    {!! file_get_contents($chartImages['section_performance']) !!}
                </div>
            </div>
            @endif

        </div>
    </div>
    @endif

    <div class="section">
        <h2>Top Performing Students</h2>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Student Number</th>
                    <th>Section</th>
                    <th>Average Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentAnalytics['topStudents'] as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['student_number'] }}</td>
                    <td>{{ $student['section'] }}</td>
                    <td>{{ $student['avgScore'] }}/5.0</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Company Placement Performance</h2>
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Total Slots</th>
                    <th>Filled Slots</th>
                    <th>Success Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placementAnalytics['companyPlacements'] as $company)
                <tr>
                    <td>{{ $company['company'] }}</td>
                    <td>{{ $company['totalSlots'] }}</td>
                    <td>{{ $company['filledSlots'] }}</td>
                    <td>{{ $company['successRate'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Section Performance Analysis</h2>
        <table>
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Total Students</th>
                    <th>Completed Assessments</th>
                    <th>Placed Students</th>
                    <th>Completion Rate</th>
                    <th>Placement Rate</th>
                    <th>Average Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sectionAnalytics as $section)
                <tr>
                    <td>{{ $section['section'] }}</td>
                    <td>{{ $section['totalStudents'] }}</td>
                    <td>{{ $section['completedAssessments'] }}</td>
                    <td>{{ $section['placedStudents'] }}</td>
                    <td>{{ $section['completionRate'] }}%</td>
                    <td>{{ $section['placementRate'] }}%</td>
                    <td>{{ $section['avgScore'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>HTE Performance Overview</h2>
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Contact Person</th>
                    <th>Total Internships</th>
                    <th>Total Slots</th>
                    <th>Filled Slots</th>
                    <th>Utilization Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hteAnalytics['htePerformance'] as $hte)
                <tr>
                    <td>{{ $hte['company_name'] }}</td>
                    <td>{{ $hte['contact_person'] }}</td>
                    <td>{{ $hte['totalInternships'] }}</td>
                    <td>{{ $hte['totalSlots'] }}</td>
                    <td>{{ $hte['filledSlots'] }}</td>
                    <td>{{ $hte['utilizationRate'] }}%</td>
                    <td>{{ $hte['is_submit'] ? 'Active' : 'Inactive' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated automatically by the Internship Management System</p>
        <p>For questions or support, please contact the system administrator</p>
    </div>
</body>
</html>
