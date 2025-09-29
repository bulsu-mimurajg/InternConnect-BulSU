@extends('emails.layout')

@section('content')
    <p>Dear {{ $studentName }},</p>
    <p>This is a reminder that you have a pending {{ $assessmentType }} assessment.</p>
    
    <div class="warning">
        <p><strong>Please complete your assessment as soon as possible.</strong></p>
    </div>
    
    <p>Log in to your dashboard to access and complete the assessment.</p>
    <p>If you have any questions, please contact your adviser.</p>
@endsection
