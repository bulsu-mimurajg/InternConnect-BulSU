<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'BULSU InternConnect' }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container { 
            max-width: 600px; 
            margin: 0 auto; 
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header { 
            background-color: #2c3e50; 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header h2 {
            margin: 10px 0 0 0;
            font-size: 18px;
            font-weight: normal;
            opacity: 0.9;
        }
        .content { 
            padding: 30px 20px; 
            background-color: #ffffff; 
        }
        .footer { 
            padding: 20px; 
            text-align: center; 
            font-size: 12px; 
            color: #666; 
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        .highlight { 
            background-color: #3498db; 
            color: white; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 5px; 
        }
        .credentials { 
            background-color: #27ae60; 
            color: white; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 5px; 
        }
        .warning { 
            background-color: #f39c12; 
            color: white; 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 3px; 
        }
        .info { 
            background-color: #3498db; 
            color: white; 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 3px; 
        }
        .login-section { 
            background-color: #27ae60; 
            color: white; 
            padding: 15px; 
            margin: 15px 0; 
            text-align: center; 
        }
        .button { 
            display: inline-block; 
            background-color: #27ae60; 
            color: white; 
            padding: 12px 24px; 
            text-decoration: none; 
            border-radius: 5px; 
            margin: 10px 0; 
            font-weight: bold;
        }
        .button:hover { 
            background-color: #229954; 
        }
        .credential-item { 
            background-color: rgba(255,255,255,0.2); 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 3px; 
        }
        .credential-item strong {
            display: inline-block;
            min-width: 120px;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin: 5px 0;
        }
        .url-box {
            word-break: break-all; 
            background-color: #ecf0f1; 
            padding: 10px; 
            border-radius: 3px; 
            font-family: monospace;
            font-size: 12px;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            .header, .content, .footer {
                padding: 20px 15px !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>BULSU InternConnect</h1>
            <h2>{{ $headerSubtitle ?? 'System Notification' }}</h2>
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <p>This is an automated message from BULSU InternConnect System.</p>
            <p>© {{ date('Y') }} Bulacan State University</p>
            <p>For support, please contact the system administrator.</p>
        </div>
    </div>
</body>
</html>
