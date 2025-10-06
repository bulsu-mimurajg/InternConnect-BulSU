<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Report - {{ $reportConfig['name'] }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #e67e22;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
        }
        .test-badge {
            display: inline-block;
            background-color: #e67e22;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .note {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin-bottom: 20px;
        }
        .data-section {
            margin-bottom: 30px;
        }
        .data-section h2 {
            margin: 0 0 15px 0;
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #e67e22;
            padding-bottom: 5px;
        }
        .json-data {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ecf0f1;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            overflow-x: auto;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $reportConfig['name'] }}</h1>
        <span class="test-badge">TEST DATA</span>
    </div>

    <div class="note">
        <strong>Test Report Notice:</strong> This report contains generated test data for demonstration purposes. 
        Generated on {{ $generatedAt->format('M d, Y H:i:s') }}.
    </div>

    <div class="data-section">
        <h2>Report Information</h2>
        <p><strong>Report Type:</strong> {{ $data['report_type'] ?? 'N/A' }}</p>
        <p><strong>Description:</strong> {{ $reportConfig['description'] ?? 'N/A' }}</p>
        <p><strong>Category:</strong> {{ $reportConfig['category'] ?? 'N/A' }}</p>
        <p><strong>Generated At:</strong> {{ $data['generated_at'] ?? $generatedAt->format('M d, Y H:i:s') }}</p>
    </div>

    @if(isset($data['parameters']))
    <div class="data-section">
        <h2>Parameters Used</h2>
        <div class="json-data">{{ json_encode($data['parameters'], JSON_PRETTY_PRINT) }}</div>
    </div>
    @endif

    @if(isset($data['message']))
    <div class="data-section">
        <h2>Message</h2>
        <p>{{ $data['message'] }}</p>
    </div>
    @endif

    @if(isset($data['note']))
    <div class="data-section">
        <h2>Note</h2>
        <p>{{ $data['note'] }}</p>
    </div>
    @endif

    <div class="data-section">
        <h2>Raw Test Data</h2>
        <div class="json-data">{{ json_encode($data, JSON_PRETTY_PRINT) }}</div>
    </div>

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('M d, Y H:i:s') }} | BULSU InternConnect Test Report</p>
        <p>This is a generic test report template for {{ $reportConfig['name'] }}.</p>
    </div>
</body>
</html>
