<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HTE Performance Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #333; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        .stats { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .stats h3 { margin-top: 0; color: #333; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .stat-item { text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #2563eb; }
        .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: auto; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; font-size: 10px; }
        .col-company { width: 25%; }
        .col-contact { width: 18%; }
        .col-status { width: 10%; }
        .col-internships { width: 12%; }
        .col-slots { width: 12%; }
        .col-rate { width: 13%; }
        .col-date { width: 12%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HTE Performance Report</h1>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    @if(isset($stats))
    <div class="stats">
        <h3>System Statistics</h3>
        <div class="stats-grid">
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
        </div>
    </div>
    @endif

    @if(isset($hteStats) && count($hteStats) > 0)
    <table>
        <thead>
            <tr>
                <th class="col-company">Company</th>
                <th class="col-contact">Contact Person</th>
                <th class="col-status">Is Submit</th>
                <th class="col-internships">Total Internships</th>
                <th class="col-internships">Active Internships</th>
                <th class="col-slots">Total Slots</th>
                <th class="col-slots">Filled Slots</th>
                <th class="col-rate">Utilization Rate</th>
                <th class="col-date">Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hteStats as $hte)
            <tr>
                <td class="col-company">{{ $hte['company_name'] }}</td>
                <td class="col-contact">{{ $hte['contact_person'] }}</td>
                <td class="col-status">{{ $hte['is_submit'] ? 'Yes' : 'No' }}</td>
                <td class="col-internships">{{ $hte['totalInternships'] }}</td>
                <td class="col-internships">{{ $hte['activeInternships'] }}</td>
                <td class="col-slots">{{ $hte['totalSlots'] }}</td>
                <td class="col-slots">{{ $hte['filledSlots'] }}</td>
                <td class="col-rate">{{ $hte['utilizationRate'] }}%</td>
                <td class="col-date">{{ $hte['created_at'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No HTE data found in the system.</p>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
