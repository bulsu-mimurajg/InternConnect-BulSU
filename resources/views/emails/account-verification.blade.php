@extends('emails.layout')

@section('content')
    <p>Dear {{ $userName }},</p>
    <p>Welcome to BULSU InternConnect! Thank you for registering with our internship management system.</p>
    
    <div class="highlight">
        <h3>Please verify your account to get started</h3>
        <p>Click the button below to verify your email address and activate your account:</p>
        <a href="{{ $verificationUrl }}" class="button">Verify My Account</a>
    </div>
    
    <p><strong>Or copy and paste this link into your browser:</strong></p>
    <div class="url-box">{{ $verificationUrl }}</div>
    
    <div class="warning">
        <h4>⚠️ Important Information:</h4>
        <ul>
            <li>This verification link will expire in 24 hours for security reasons</li>
            <li>If you didn't create an account with BULSU InternConnect, please ignore this email</li>
        </ul>
    </div>
@endsection
