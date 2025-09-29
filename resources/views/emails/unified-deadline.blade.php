<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $urgencyLevel['title'] }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .urgency-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }
        .urgency-critical {
            background-color: #dc3545;
            color: white;
        }
        .urgency-urgent {
            background-color: #fd7e14;
            color: white;
        }
        .urgency-high {
            background-color: #ffc107;
            color: #212529;
        }
        .urgency-medium {
            background-color: #17a2b8;
            color: white;
        }
        .urgency-normal {
            background-color: #6c757d;
            color: white;
        }
        .deadline-info {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        .deadline-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        .deadline-details {
            color: #6c757d;
            margin: 5px 0;
        }
        .time-remaining {
            font-size: 18px;
            font-weight: 600;
            color: #e74c3c;
            margin: 10px 0;
        }
        .action-section {
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .action-title {
            font-weight: 600;
            color: #0c5460;
            margin: 0 0 10px 0;
        }
        .action-text {
            color: #0c5460;
            margin: 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        .role-specific {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .role-specific-title {
            font-weight: 600;
            color: #856404;
            margin: 0 0 10px 0;
        }
        .role-specific-text {
            color: #856404;
            margin: 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 20px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .header, .content {
                padding: 20px;
            }
            .deadline-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('bulsu_logo_svg.svg') }}" alt="BulSU Logo" class="logo">
            <h1>{{ $urgencyLevel['title'] }}</h1>
        </div>
        
        <div class="content">
            <div class="urgency-badge urgency-{{ $urgencyLevel['level'] }}">
                {{ ucfirst($urgencyLevel['level']) }} Priority
            </div>
            
            <p>Hello <strong>{{ $userDisplayName }}</strong>!</p>
            
            <p>{{ $urgencyLevel['message'] }}</p>
            
            <div class="deadline-info">
                <div class="deadline-title">{{ $deadlineName }}</div>
                <div class="deadline-details">
                    <strong>Category:</strong> {{ $categoryDisplay }}<br>
                    <strong>Deadline Date:</strong> {{ $deadlineDate->format('F d, Y \a\t g:i A') }}<br>
                    <strong>Status:</strong> {{ $timeRemainingText }}
                </div>
                <div class="time-remaining">
                    @if($daysRemaining !== null)
                        @if($daysRemaining == 1)
                            Due Tomorrow!
                        @elseif($daysRemaining == 0)
                            Due Today!
                        @else
                            {{ $daysRemaining }} Days Remaining
                        @endif
                    @elseif($hoursRemaining !== null)
                        @if($hoursRemaining == 1)
                            1 Hour Remaining!
                        @else
                            {{ $hoursRemaining }} Hours Remaining!
                        @endif
                    @endif
                </div>
            </div>
            
            <div class="action-section">
                <div class="action-title">Required Action</div>
                <p class="action-text">{{ $actionText }}</p>
            </div>
            
            @if($roleSpecificContent)
            <div class="role-specific">
                <div class="role-specific-title">Role-Specific Information</div>
                <p class="role-specific-text">{{ $roleSpecificContent }}</p>
            </div>
            @endif
            
            <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="cta-button">View Details & Take Action</a>
            </div>
            
            <p>Please ensure you complete the required actions before the deadline. If you have any questions, please contact the system administrator.</p>
        </div>
        
        <div class="footer">
            <p><strong>InternConnect System</strong></p>
            <p>Bulacan State University</p>
            <p>This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
