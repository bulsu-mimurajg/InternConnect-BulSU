<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Compatibility Report - {{ $internship->position_title }}</title>
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
        .col-rank { width: 8%; }
        .col-name { width: 25%; }
        .col-section { width: 20%; }
        .col-score { width: 15%; }
        .col-status { width: 15%; }
        .col-analysis { width: 17%; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
        .page-break { page-break-before: always; }
        .score-excellent { color: #059669; font-weight: bold; }
        .score-good { color: #f59e0b; font-weight: bold; }
        .score-fair { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Compatibility Report</h1>
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
                    <div class="stat-label">Available Slots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->count() }}</div>
                    <div class="stat-label">Total Candidates</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ round($placements->avg('compatibility_score'), 2) }}</div>
                    <div class="stat-label">Average Compatibility</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $placements->where('compatibility_score', '>=', 80)->count() }}</div>
                    <div class="stat-label">High Compatibility (≥80)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compatibility Analysis -->
    <div class="section">
        <h2>Compatibility Analysis</h2>
        <div class="stats">
            <h3>Score Distribution</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value score-excellent">{{ $placements->where('compatibility_score', '>=', 80)->count() }}</div>
                    <div class="stat-label">Excellent (≥80)</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value score-good">{{ $placements->whereBetween('compatibility_score', [60, 79])->count() }}</div>
                    <div class="stat-label">Good (60-79)</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value score-fair">{{ $placements->where('compatibility_score', '<', 60)->count() }}</div>
                    <div class="stat-label">Fair (<60)</div>
                </div>
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
            </div>
        </div>
    </div>

    <!-- Student Compatibility Rankings -->
    <div class="section page-break">
        <h2>Student Compatibility Rankings</h2>
        @if($placements && $placements->count() > 0)
        <table>
            <thead>
                <tr>
                    <th class="col-rank">Rank</th>
                    <th class="col-name">Student Name</th>
                    <th class="col-section">Section</th>
                    <th class="col-score">Compatibility Score</th>
                    <th class="col-status">Status</th>
                    <th class="col-analysis">Compatibility Level</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placements as $index => $placement)
                <tr>
                    <td class="col-rank">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $placement['student_name'] }}</td>
                    <td class="col-section">{{ $placement['section'] }}</td>
                    <td class="col-score {{ $placement['compatibility_score'] >= 80 ? 'score-excellent' : ($placement['compatibility_score'] >= 60 ? 'score-good' : 'score-fair') }}">
                        {{ $placement['compatibility_score'] }}
                    </td>
                    <td class="col-status">{{ ucfirst($placement['status']) }}</td>
                    <td class="col-analysis">
                        @if($placement['compatibility_score'] >= 80)
                            <span class="score-excellent">Excellent Match</span>
                        @elseif($placement['compatibility_score'] >= 60)
                            <span class="score-good">Good Match</span>
                        @else
                            <span class="score-fair">Fair Match</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No student compatibility data available for this internship.</p>
        @endif
    </div>

    <!-- Top Candidates -->
    @if($placements && $placements->count() > 0)
    <div class="section page-break">
        <h2>Top Compatibility Candidates</h2>
        <p>Students with the highest compatibility scores for this internship position:</p>
        
        <table>
            <thead>
                <tr>
                    <th class="col-rank">Rank</th>
                    <th class="col-name">Student Name</th>
                    <th class="col-section">Section</th>
                    <th class="col-score">Score</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placements->take(10) as $index => $placement)
                <tr>
                    <td class="col-rank">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $placement['student_name'] }}</td>
                    <td class="col-section">{{ $placement['section'] }}</td>
                    <td class="col-score score-excellent">{{ $placement['compatibility_score'] }}</td>
                    <td class="col-status">{{ ucfirst($placement['status']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Section Analysis -->
    <div class="section">
        <h2>Section Performance Analysis</h2>
        <table>
            <thead>
                <tr>
                    <th class="col-section">Section</th>
                    <th class="col-rank">Students</th>
                    <th class="col-score">Average Score</th>
                    <th class="col-score">Highest Score</th>
                    <th class="col-rank">Top Performers</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placements->groupBy('section') as $section => $students)
                <tr>
                    <td class="col-section">{{ $section }}</td>
                    <td class="col-rank">{{ $students->count() }}</td>
                    <td class="col-score">{{ round($students->avg('compatibility_score'), 2) }}</td>
                    <td class="col-score score-excellent">{{ $students->max('compatibility_score') }}</td>
                    <td class="col-rank">{{ $students->where('compatibility_score', '>=', 80)->count() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Compatibility Insights -->
    <div class="section">
        <h2>Compatibility Insights</h2>
        <div class="stats">
            <h3>Key Insights</h3>
            <ul style="margin: 0; padding-left: 20px;">
                <li><strong>Total Candidates:</strong> {{ $placements->count() }} students evaluated for this position</li>
                <li><strong>High Compatibility:</strong> {{ $placements->where('compatibility_score', '>=', 80)->count() }} students ({{ round(($placements->where('compatibility_score', '>=', 80)->count() / max($placements->count(), 1)) * 100, 1) }}%) have excellent compatibility scores</li>
                <li><strong>Score Range:</strong> {{ $placements->min('compatibility_score') }} - {{ $placements->max('compatibility_score') }} ({{ round($placements->max('compatibility_score') - $placements->min('compatibility_score'), 1) }} point spread)</li>
                <li><strong>Best Performing Section:</strong> {{ $placements->groupBy('section')->sortByDesc(fn($students) => $students->avg('compatibility_score'))->keys()->first() ?? 'N/A' }} with an average score of {{ round($placements->groupBy('section')->sortByDesc(fn($students) => $students->avg('compatibility_score'))->first()->avg('compatibility_score') ?? 0, 2) }}</li>
                <li><strong>Recommendation:</strong> 
                    @if($placements->where('compatibility_score', '>=', 80)->count() >= $internship->slot_count)
                        Excellent candidate pool with sufficient high-compatibility students for all available slots.
                    @elseif($placements->where('compatibility_score', '>=', 60)->count() >= $internship->slot_count)
                        Good candidate pool with adequate compatibility for filling all positions.
                    @else
                        Limited candidate pool; consider reviewing matching criteria or expanding search.
                    @endif
                </li>
            </ul>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the InternConnect System</p>
    </div>
</body>
</html>
