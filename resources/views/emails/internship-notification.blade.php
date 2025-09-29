@extends('emails.layout')

@section('content')
    <p>Dear {{ $studentName }},</p>
    <p>Congratulations! You have been successfully matched with an internship opportunity.</p>
    
    <div class="highlight">
        <h3>Internship Details:</h3>
        <div class="credential-item">
            <strong>Company:</strong> {{ $internshipDetails['company_name'] }}
        </div>
        <div class="credential-item">
            <strong>Position:</strong> {{ $internshipDetails['position'] }}
        </div>
        <div class="credential-item">
            <strong>Duration:</strong> {{ $internshipDetails['duration'] }}
        </div>
        <div class="credential-item">
            <strong>Start Date:</strong> {{ $internshipDetails['start_date'] }}
        </div>
    </div>
    
    <p>Please check your dashboard for more details and next steps.</p>
    <p>If you have any questions, please contact your adviser or the internship coordinator.</p>
@endsection
