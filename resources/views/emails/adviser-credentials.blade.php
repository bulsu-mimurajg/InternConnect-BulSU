@extends('emails.layout')

@section('content')
    <p style="font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 15px;">Welcome, {{ $adviserName }}!</p>
    <p style="margin-bottom: 20px;">Your adviser account has been successfully created by the system administrator.</p>
    
    <div class="highlight">
        <h3>Your Account Credentials</h3>
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
    
    <div class="button-container">
        <a href="{{ $loginUrl }}" class="button">Access Adviser Dashboard</a>
    </div>
    
    <div class="warning">
        <h4>Important</h4>
        <ul>
            <li>Change your password after your first login</li>
            <li>Keep your credentials secure</li>
            <li>Sections: {{ implode(', ', $sections) }}</li>
        </ul>
    </div>
@endsection
