<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Overview Report - {{ $hte->company_name }}</title>
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
        .col-position { width: 30%; }
        .col-department { width: 20%; }
        .col-slots { width: 15%; }
        .col-status { width: 15%; }
        .col-created { width: 20%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Company Overview Report</h1>
        <p>{{ $hte->company_name }}</p>
        <p>Generated on {{ $generatedAt }}</p>
    </div>

    <!-- Company Information -->
    <div class="section">
        <h2>Company Information</h2>
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $hte->company_name }}</div>
                    <div class="stat-label">Company Name</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $hte->cperson_fname . ' ' . $hte->cperson_lname }}</div>
                    <div class="stat-label">Contact Person</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $hte->company_email }}</div>
                    <div class="stat-label">Email</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $hte->cperson_contactnum }}</div>
                    <div class="stat-label">Phone</div>
                </div>
            </div>
        </div>
        <p><strong>Address:</strong> {{ $hte->company_address }}</p>
        <p><strong>Contact Position:</strong> {{ $hte->cperson_position }}</p>
    </div>

    <!-- Company Statistics -->
    <div class="section">
        <h2>Company Statistics</h2>
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $totalSlots }}</div>
                    <div class="stat-label">Total Slots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $activeInternships }}</div>
                    <div class="stat-label">Active Internships</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placedStudents }}</div>
                    <div class="stat-label">Placed Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $utilizationRate }}%</div>
                    <div class="stat-label">Utilization Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Internship Details -->
    <div class="section page-break">
        <h2>Internship Details</h2>
        @if($internships && $internships->count() > 0)
        <table>
            <thead>
                <tr>
                    <th class="col-position">Position Title</th>
                    <th class="col-department">Department</th>
                    <th class="col-slots">Available Slots</th>
                    <th class="col-status">Status</th>
                    <th class="col-created">Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($internships as $internship)
                <tr>
                    <td class="col-position">{{ $internship->position_title }}</td>
                    <td class="col-department">{{ $internship->department }}</td>
                    <td class="col-slots">{{ $internship->slot_count }}</td>
                    <td class="col-status">{{ $internship->is_active ? 'Active' : 'Inactive' }}</td>
                    <td class="col-created">{{ $internship->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No internships found for this company.</p>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
