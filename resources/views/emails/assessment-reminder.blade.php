@extends('emails.layout')

@section('content')
    <p style="font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 15px;">Hello, {{ $studentName }}!</p>
    <p style="margin-bottom: 20px;">This is a reminder that you have a pending {{ $assessmentType }} assessment.</p>
    
    <div class="warning">
        <p><strong>Please complete your assessment as soon as possible.</strong></p>
    </div>
    
    <div class="button-container">
        <a href="{{ $dashboardUrl ?? '#' }}" class="button">Complete Assessment</a>
    </div>
    
    <p style="margin: 20px 0 15px 0;">Log in to your dashboard to access and complete the assessment.</p>
    <p style="margin-bottom: 0;">If you have any questions, please contact your adviser.</p>
@endsection
