<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'BULSU InternConnect' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #ffffff;
            color: #2c3e50;
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid #e67e22;
        }
        .header-logo {
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
        }
        .header h2 {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 400;
            color: #7f8c8d;
        }
        .header-subtitle {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #95a5a6;
        }
        .content {
            padding: 20px;
            background-color: #ffffff;
            font-size: 14px;
            line-height: 1.6;
            color: #2c3e50;
        }
        .highlight {
            background-color: #f8f9fa;
            color: #2c3e50;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #e67e22;
        }
        .highlight h3 {
            margin: 0 0 15px 0;
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #e67e22;
            padding-bottom: 5px;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }
        .warning h4 {
            margin: 0 0 10px 0;
            color: #856404;
            font-size: 16px;
            font-weight: bold;
        }
        .button {
            display: inline-block;
            background-color: #e67e22 !important;
            color: white !important;
            padding: 12px 24px;
            text-decoration: none !important;
            border-radius: 8px;
            margin: 12px auto;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            text-align: center;
        }
        .button-container {
            text-align: center;
            margin: 15px 0;
        }
        .button:hover {
            background-color: #d35400 !important;
            transform: translateY(-1px);
        }
        .button:visited {
            color: white !important;
        }
        .button:link {
            color: white !important;
        }
        .credentials {
            background-color: #e67e22;
            color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .credential-item {
            background-color: rgba(255,255,255,0.2);
            padding: 12px;
            margin: 8px 0;
            border-radius: 4px;
        }
        .credential-item strong {
            display: inline-block;
            min-width: 120px;
            font-weight: bold;
        }
        .info {
            background-color: #34495e;
            color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .info h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: bold;
        }
        ul {
            padding-left: 20px;
            margin: 10px 0;
        }
        li {
            margin: 5px 0;
        }
        .url-box {
            word-break: break-all;
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            border: 1px solid #ecf0f1;
            margin: 12px 0;
            color: #7f8c8d;
        }
        .footer {
            background-color: #f8f9fa;
            border-top: 1px solid #ecf0f1;
            padding: 20px;
            margin-top: 30px;
            font-size: 12px;
            color: #7f8c8d;
        }
        .footer-content {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .footer-section {
            flex: 1;
        }
        .footer-section h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }
        .footer-section p {
            margin: 5px 0;
            line-height: 1.5;
        }
        .footer-bottom {
            border-top: 1px solid #ecf0f1;
            padding-top: 15px;
            text-align: center;
            font-size: 11px;
            color: #95a5a6;
        }
        .footer-bottom p {
            margin: 3px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            .header, .content, .footer {
                padding: 15px !important;
            }
            .footer-content {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="header-logo">
                <div style="font-size: 20px; font-weight: bold; color: #e67e22; margin: 0 0 4px 0; text-align: center; letter-spacing: 1px; text-transform: uppercase;">
                    BULSU
                </div>
                <div style="font-size: 14px; color: #7f8c8d; text-align: center; letter-spacing: 0.5px; margin-bottom: 8px;">
                    Bulacan State University
                </div>
            </div>
            <h1>InternConnect BSIT</h1>
            <div class="header-subtitle">{{ $headerSubtitle ?? 'InternConnect BSIT' }}</div>
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <p>
                        Bulacan State University<br>
                        College of Information and Communications Technology<br>
                        Malolos, Bulacan, Philippines
                    </p>
                </div>

                <div class="footer-section">
                    <h4>InternConnect BSIT</h4>
                    <p>
                        Connecting students with industry partners<br>
                        for meaningful internship experiences.
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Bulacan State University. All rights reserved.</p>
                <p>
                    This is an automated message from BULSU InternConnect.
                    Please do not reply to this email.
                </p>
            </div>
        </div>

    </div>
</body>
</html>
