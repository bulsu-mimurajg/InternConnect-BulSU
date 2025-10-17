<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTE Profile Report</title>
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
            margin-bottom: 25px;
            background: #fafafa;
            padding: 20px;
            display: flex;
            gap: 20px;
        }
        .stats-column {
            flex: 1;
            width: 50%;
        }
        .stat-item {
            margin-bottom: 15px;
            position: relative;
            padding-left: 20px;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            font-weight: 600;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .company-info {
            background: #f8f9fa;
            padding: 20px;
            
            margin-bottom: 25px;
        }
        .company-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .info-grid {
            margin-bottom: 25px; background: #fafafa; padding: 20px;  position: relative;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #34495e;
            font-size: 14px;
        }
        .info-value {
            color: #2c3e50;
            font-size: 14px;
            margin-top: 2px;
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
        

        .department-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .department-title {
            color: #2c3e50;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            background: #ecf0f1;
            padding: 8px 12px;
            
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
            .stats {
            margin-bottom: 25px;
            background: #fafafa;
            padding: 20px;
            display: flex;
            gap: 20px;
        }
        .stats-column {
            flex: 1;
            width: 50%;
        }
        }
    
        
        
        .stat-item {
            margin-bottom: 15px;
            position: relative;
            padding-left: 20px;
        }
        .stat-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            width: 8px;
            height: 1px;
            background: #e67e22;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            font-weight: 600;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
    
        @media print {
            .stats { display: flex; }
            .stats-column { width: 50%; }
        }
        .stats-column {
            flex: 1;
            width: 50%;
        }
        }
        @media (max-width: 768px) {
            .stats { flex-direction: column; }
            .stats-column { width: 100%; }
        }
        .stats-column {
            flex: 1;
            width: 50%;
        }
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
                <h1>HTE Profile Report</h1>
                <div class="subtitle">{{ $hte->company_name }}</div>
                <div class="date">Generated on {{ now()->format('F d, Y \a\t g:i A') }}</div>
            </div>

            <!-- Company Information -->
            <div class="section">
                <h2 class="section-title">Company Information</h2>
                <div class="company-info">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Company Name:</div>
                            <div class="info-value">{{ $hte->company_name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Company Address:</div>
                            <div class="info-value">{{ $hte->company_address ?? 'Not specified' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Company Email:</div>
                            <div class="info-value">{{ $hte->company_email ?? 'Not specified' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Contact Person:</div>
                            <div class="info-value">
                                @if($hte->cperson_fname || $hte->cperson_lname)
                                    {{ trim($hte->cperson_fname . ' ' . $hte->cperson_lname) }}
                                @else
                                    Not specified
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Contact Position:</div>
                            <div class="info-value">{{ $hte->cperson_position ?? 'Not specified' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Contact Number:</div>
                            <div class="info-value">{{ $hte->cperson_contactnum ?? 'Not specified' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status:</div>
                            <div class="info-value">
                                <span style="color: {{ $hte->is_active ? '#27ae60' : '#e74c3c' }}; font-weight: bold;">
                                    {{ $hte->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Profile Submitted:</div>
                            <div class="info-value">
                                <span style="color: {{ $hte->is_submit ? '#27ae60' : '#e74c3c' }}; font-weight: bold;">
                                    {{ $hte->is_submit ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="section">
                <h2 class="section-title">Statistics Overview</h2>
                <div class="stats">
                    @foreach($stats as $stat)
                        <div class="stat-item">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Department Breakdown -->
            @if($departmentStats->count() > 0)
            <div class="section">
                <h2 class="section-title">Department Breakdown</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Internships</th>
                            <th>Total Slots</th>
                            <th>Used Slots</th>
                            <th>Utilization Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentStats as $dept)
                            <tr>
                                <td><strong>{{ $dept['department'] }}</strong></td>
                                <td>{{ $dept['internships_count'] }}</td>
                                <td>{{ $dept['total_slots'] }}</td>
                                <td>{{ $dept['used_slots'] }}</td>
                                <td>
                                    <span style="color: {{ $dept['utilization_rate'] >= 70 ? '#27ae60' : ($dept['utilization_rate'] >= 40 ? '#f39c12' : '#e74c3c') }}; font-weight: bold;">
                                        {{ number_format($dept['utilization_rate'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Internship Details -->
            <div class="section">
                <h2 class="section-title">Internship Offerings</h2>
                @if($internships->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Position Title</th>
                                <th>Department</th>
                                <th>Total Slots</th>
                                <th>Used Slots</th>
                                <th>Available Slots</th>
                                <th>Utilization Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($internships as $internship)
                                @php
                                    $usedSlots = $internship->studentPlacements->where('status', 'approved')->count();
                                    $utilizationRate = $internship->slot_count > 0 ? ($usedSlots / $internship->slot_count) * 100 : 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $internship->position_title }}</strong></td>
                                    <td>{{ $internship->department }}</td>
                                    <td>{{ $internship->slot_count }}</td>
                                    <td>{{ $usedSlots }}</td>
                                    <td>{{ $internship->slot_count - $usedSlots }}</td>
                                    <td>
                                        <span style="color: {{ $utilizationRate >= 70 ? '#27ae60' : ($utilizationRate >= 40 ? '#f39c12' : '#e74c3c') }}; font-weight: bold;">
                                            {{ number_format($utilizationRate, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align: center; color: #7f8c8d; font-style: italic; padding: 20px;">
                        No internships available for this HTE.
                    </p>
                @endif
            </div>

            <!-- Placed Students Summary -->
            @if($placedStudents->count() > 0)
            <div class="section">
                <h2 class="section-title">Placed Students Summary</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Student Number</th>
                            <th>Section</th>
                            <th>Position</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($placedStudents->take(20) as $student)
                            @php
                                $placement = $student->placements->where('status', 'approved')->first();
                                $internship = $placement ? $placement->internship : null;
                            @endphp
                            <tr>
                                <td>
                                    {{ $student->last_name }}, {{ $student->first_name }}
                                    @if($student->middle_name)
                                        {{ strtoupper(substr($student->middle_name, 0, 1)) }}.
                                    @endif
                                </td>
                                <td>{{ $student->student_number }}</td>
                                <td>{{ $student->section->section_name ?? 'N/A' }}</td>
                                <td>{{ $internship ? $internship->position_title : 'N/A' }}</td>
                                <td>{{ $internship ? $internship->department : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($placedStudents->count() > 20)
                    <p style="text-align: center; color: #7f8c8d; font-style: italic; margin-top: 10px;">
                        Showing first 20 of {{ $placedStudents->count() }} placed students.
                    </p>
                @endif
            </div>
            @endif

            <!-- Footer -->
            <div class="footer">
                <p>This report was generated by the InternConnect BSIT system.</p>
                <p>For questions or concerns, please contact the system administrator.</p>
            </div>
        </div>
    </div>
</body>
</html>
