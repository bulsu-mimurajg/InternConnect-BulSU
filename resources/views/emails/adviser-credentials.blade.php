@extends('emails.layout')

@section('content')
    <p>Dear {{ $adviserName }},</p>
    <p>Welcome to BULSU InternConnect! Your adviser account has been successfully created by the system administrator.</p>
    
    <div class="credentials">
        <h3>🔐 Your Account Credentials</h3>
        <div class="credential-item">
            <strong>Username:</strong> {{ $username }}
        </div>
        <div class="credential-item">
            <strong>Password:</strong> {{ $password }}
        </div>
        <div class="credential-item">
            <strong>Email:</strong> {{ $email }}
        </div>
    </div>
    
    <div class="info">
        <h3>📚 Your Assigned Sections</h3>
        <p><strong>Sections:</strong> {{ implode(', ', $sections) }}</p>
        <p>You will be able to manage students in these assigned sections.</p>
    </div>
    
    <div class="warning">
        <h4>⚠️ Important Security Information:</h4>
        <ul>
            <li><strong>Change your password</strong> after your first login for security</li>
            <li>Keep your credentials secure and do not share them</li>
            <li>Review your assigned sections and student lists</li>
            <li>Complete your profile information in the dashboard</li>
        </ul>
    </div>
    
    <p><strong>What you can do as an Adviser:</strong></p>
    <ul>
        <li>Manage and monitor students in your assigned sections</li>
        <li>Review student assessments and performance</li>
        <li>Track internship placements and progress</li>
        <li>Communicate with students and HTEs</li>
        <li>Generate reports and analytics for your sections</li>
        <li>Approve student internship applications</li>
    </ul>
    
    <p>If you have any questions about your assigned sections or need assistance, please contact the system administrator.</p>
@endsection
