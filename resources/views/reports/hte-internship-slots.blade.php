<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Slots Report - {{ $hte->company_name }}</title>
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
        .col-total { width: 12%; }
        .col-filled { width: 12%; }
        .col-available { width: 12%; }
        .col-rate { width: 12%; }
        .col-status { width: 12%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
        .page-break { page-break-before: always; }
        .utilization-high { color: #059669; font-weight: bold; }
        .utilization-medium { color: #f59e0b; font-weight: bold; }
        .utilization-low { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Internship Slots Report</h1>
        <p>{{ $hte->company_name }}</p>
        <p>Generated on {{ $generatedAt }}</p>
    </div>

    <!-- Summary Statistics -->
    <div class="section">
        <h2>Overall Slot Utilization</h2>
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $internships->sum('slot_count') }}</div>
                    <div class="stat-label">Total Slots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $internships->sum('filled_slots') }}</div>
                    <div class="stat-label">Filled Slots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $internships->sum('available_slots') }}</div>
                    <div class="stat-label">Available Slots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ round(($internships->sum('filled_slots') / max($internships->sum('slot_count'), 1)) * 100, 1) }}%</div>
                    <div class="stat-label">Overall Utilization</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $internships->where('is_active', true)->count() }}</div>
                    <div class="stat-label">Active Internships</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $internships->count() }}</div>
                    <div class="stat-label">Total Internships</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Individual Internship Details -->
    <div class="section page-break">
        <h2>Individual Internship Slot Utilization</h2>
        @if($internships && $internships->count() > 0)
        <table>
            <thead>
                <tr>
                    <th class="col-position">Position Title</th>
                    <th class="col-department">Department</th>
                    <th class="col-total">Total Slots</th>
                    <th class="col-filled">Filled Slots</th>
                    <th class="col-available">Available Slots</th>
                    <th class="col-rate">Utilization Rate</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($internships as $internship)
                <tr>
                    <td class="col-position">{{ $internship['position_title'] }}</td>
                    <td class="col-department">{{ $internship['department'] }}</td>
                    <td class="col-total">{{ $internship['slot_count'] }}</td>
                    <td class="col-filled">{{ $internship['filled_slots'] }}</td>
                    <td class="col-available">{{ $internship['available_slots'] }}</td>
                    <td class="col-rate {{ $internship['utilization_rate'] >= 80 ? 'utilization-high' : ($internship['utilization_rate'] >= 50 ? 'utilization-medium' : 'utilization-low') }}">
                        {{ $internship['utilization_rate'] }}%
                    </td>
                    <td class="col-status">{{ $internship['is_active'] ? 'Active' : 'Inactive' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No internships found for this company.</p>
        @endif
    </div>

    <!-- Utilization Analysis -->
    @if($internships && $internships->count() > 0)
    <div class="section page-break">
        <h2>Utilization Analysis</h2>
        <div class="stats">
            <h3>Performance Categories</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value utilization-high">{{ $internships->where('utilization_rate', '>=', 80)->count() }}</div>
                    <div class="stat-label">High Utilization (≥80%)</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value utilization-medium">{{ $internships->whereBetween('utilization_rate', [50, 79])->count() }}</div>
                    <div class="stat-label">Medium Utilization (50-79%)</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value utilization-low">{{ $internships->where('utilization_rate', '<', 50)->count() }}</div>
                    <div class="stat-label">Low Utilization (<50%)</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $internships->where('filled_slots', 0)->count() }}</div>
                    <div class="stat-label">No Placements Yet</div>
                </div>
            </div>
        </div>

        <h3>Top Performing Internships</h3>
        <table>
            <thead>
                <tr>
                    <th class="col-position">Position</th>
                    <th class="col-department">Department</th>
                    <th class="col-rate">Utilization Rate</th>
                    <th class="col-filled">Filled/Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($internships->sortByDesc('utilization_rate')->take(5) as $internship)
                <tr>
                    <td class="col-position">{{ $internship['position_title'] }}</td>
                    <td class="col-department">{{ $internship['department'] }}</td>
                    <td class="col-rate utilization-high">{{ $internship['utilization_rate'] }}%</td>
                    <td class="col-filled">{{ $internship['filled_slots'] }}/{{ $internship['slot_count'] }}</td>
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
