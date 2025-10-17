<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template Preview - BULSU InternConnect</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background-color: #e67e22;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .template-card {
            background-color: #f8f9fa;
            border: 1px solid #ecf0f1;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid #e67e22;
        }
        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left-color: #d35400;
        }
        .template-card h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 18px;
            font-weight: bold;
        }
        .template-card p {
            margin: 0 0 15px 0;
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.5;
        }
        .template-card a {
            display: inline-block;
            background-color: #e67e22;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .template-card a:hover {
            background-color: #d35400;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #e67e22;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            color: #d35400;
        }
        .info-box {
            background-color: #e8f4f8;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #0c5460;
            font-size: 16px;
            font-weight: bold;
        }
        .info-box p {
            margin: 0;
            color: #0c5460;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Email Template Preview</h1>
            <p>BULSU InternConnect - Test all email templates with sample data</p>
        </div>
        
        <div class="content">
            <a href="{{ route('admin.dashboard') }}" class="back-link">← Back to Admin Dashboard</a>
            
            <div class="info-box">
                <h4>📧 Email Template Testing</h4>
                <p>Click on any template below to preview how the email will look with sample data. All templates use the updated styling that matches the comprehensive report design system.</p>
            </div>

            <div class="templates-grid">
                @foreach($emailTemplates as $template)
                <div class="template-card">
                    <h3>{{ $template['name'] }}</h3>
                    <p>{{ $template['description'] }}</p>
                    <a href="{{ route($template['route']) }}" target="_blank">Preview Template</a>
                </div>
                @endforeach
            </div>

            <div class="info-box">
                <h4>🎨 Design Features</h4>
                <p><strong>Updated Styling:</strong> All emails now use the comprehensive report's design system with Segoe UI font, professional color scheme (#2c3e50, #e67e22), and consistent spacing. The layout includes a centralized footer with BULSU contact information and maintains mobile-responsive design.</p>
            </div>
        </div>
    </div>
</body>
</html>
