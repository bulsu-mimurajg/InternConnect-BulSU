@extends('emails.layout')

@section('content')
    <p style="font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 15px;">Hello, {{ $userName }}!</p>
    <p style="margin-bottom: 20px;">We received a request to reset your password for your BULSU InternConnect account.</p>
    
    <div class="highlight">
        <h3>Reset Your Password</h3>
        <p style="margin-bottom: 15px;">Click the button below to reset your password:</p>
        <div class="button-container">
            <a href="{{ $resetUrl }}" class="button">Reset Password</a>
        </div>
    </div>
    
    <div class="warning">
        <h4>Important</h4>
        <ul>
            <li>This password reset link will expire in 60 minutes</li>
            <li>If you didn't request this password reset, please ignore this email</li>
        </ul>
    </div>
@endsection
