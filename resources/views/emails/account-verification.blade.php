@extends('emails.layout')

@section('content')
    <p style="font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 15px;">Welcome, {{ $userName }}!</p>
    <p style="margin-bottom: 20px;">Thank you for registering with BULSU InternConnect. Please verify your email address to complete your registration.</p>
    
    <div class="highlight">
        <h3>Verify Your Account</h3>
        <p style="margin-bottom: 15px;">Click the button below to verify your email address:</p>
        <div class="button-container">
            <a href="{{ $verificationUrl }}" class="button">Verify Account</a>
        </div>
    </div>
    
    <p style="margin: 15px 0 8px 0; font-weight: bold; color: #7f8c8d;">Or copy this link:</p>
    <div class="url-box">{{ $verificationUrl }}</div>
    
    <div class="warning">
        <h4>Important</h4>
        <ul>
            <li>This link expires in 24 hours</li>
            <li>Ignore this email if you didn't create an account</li>
        </ul>
    </div>
@endsection
