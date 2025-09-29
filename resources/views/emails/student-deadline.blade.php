@extends('emails.layout')

@section('content')
    <p>Dear Student,</p>
    
    @if($daysRemaining === 1)
        <div class="warning">
            <h3>⚠️ Urgent: Assessment Deadline Tomorrow!</h3>
            <p>Your student assessment deadline is tomorrow ({{ $formattedDate }}).</p>
            <p><strong>Please complete your assessment immediately to avoid missing the deadline.</strong></p>
        </div>
    @elseif(in_array($daysRemaining, [3, 5]))
        <div class="info">
            <h3>📅 Assessment Deadline Reminder</h3>
            <p>Your student assessment deadline is in {{ $daysRemaining }} days ({{ $formattedDate }}).</p>
            <p>Please prepare and submit your assessment soon to avoid any last-minute issues.</p>
        </div>
    @elseif($hoursRemaining && $hoursRemaining < 24)
        <div class="warning">
            <h3>⚠️ Assessment Deadline in {{ $hoursRemaining }} Hours!</h3>
            <p>Your student assessment deadline is in {{ $hoursRemaining }} hours ({{ $formattedDate }}).</p>
            <p><strong>Please submit your assessment immediately to avoid missing the deadline.</strong></p>
        </div>
    @else
        <div class="info">
            <h3>📅 Assessment Deadline Update</h3>
            <p>Your student assessment deadline has been updated to {{ $formattedDate }}.</p>
            <p>Please ensure you complete your assessment before the deadline.</p>
        </div>
    @endif
    
    <div class="login-section">
        <h3>🚀 Complete Your Assessment</h3>
        <p>Click the button below to access your assessment:</p>
        <a href="{{ url('/assessment') }}" class="button">Complete Assessment</a>
    </div>
    
    @if($daysRemaining === 1 || ($hoursRemaining && $hoursRemaining < 24))
        <div class="warning">
            <h4>⚠️ Important Reminder:</h4>
            <ul>
                <li>This is your final reminder before the deadline</li>
                <li>Late submissions may not be accepted</li>
                <li>If you have already completed your assessment, please ignore this email</li>
            </ul>
        </div>
    @else
        <div class="info">
            <h4>📋 Assessment Guidelines:</h4>
            <ul>
                <li>Read all questions carefully before answering</li>
                <li>Ensure you have a stable internet connection</li>
                <li>Save your progress regularly</li>
                <li>Submit before the deadline to avoid any issues</li>
            </ul>
        </div>
    @endif
    
    <p>If you have any questions or need assistance, please contact your adviser or the system administrator.</p>
@endsection
