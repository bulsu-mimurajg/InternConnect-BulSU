@extends('emails.layout')

@section('content')
    <p>Dear {{ $userName }},</p>
    <p>We received a request to reset your password for your BULSU InternConnect account.</p>
    
    <div class="highlight">
        <h3>Reset Your Password</h3>
        <p>Click the button below to reset your password:</p>
        <a href="{{ $resetUrl }}" class="button">Reset Password</a>
    </div>
    
    <p><strong>Or copy and paste this link into your browser:</strong></p>
    <div class="url-box">{{ $resetUrl }}</div>
    
    <div class="warning">
        <h4>⚠️ Important Security Information:</h4>
        <ul>
            <li>This password reset link will expire in 60 minutes</li>
            <li>If you didn't request this password reset, please ignore this email</li>
            <li>Your password will remain unchanged until you create a new one</li>
        </ul>
    </div>
    
    <p>If you're having trouble clicking the button, copy and paste the URL above into your web browser.</p>
@endsection
