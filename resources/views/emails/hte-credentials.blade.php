@extends('emails.layout')

@section('content')
    <p>Dear {{ $companyName ?: 'HTE Representative' }},</p>
    <p>Welcome to BULSU InternConnect! Your Host Training Establishment (HTE) account has been successfully created by the system administrator.</p>
    
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
    
    <div class="warning">
        <h4>⚠️ Important Security Information:</h4>
        <ul>
            <li><strong>Change your password</strong> after your first login for security</li>
            <li>Keep your credentials secure and do not share them</li>
            <li>Complete your company profile information in the dashboard</li>
            <li>You can submit internship opportunities for students</li>
        </ul>
    </div>
    
    <p><strong>What you can do as an HTE:</strong></p>
    <ul>
        <li>Submit internship opportunities for BULSU students</li>
        <li>Manage your company profile and information</li>
        <li>Review and select students for internships</li>
        <li>Track internship progress and evaluations</li>
    </ul>
    
    <p>If you have any questions or need assistance, please contact the BULSU InternConnect support team.</p>
@endsection
