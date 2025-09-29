@extends('emails.layout')

@section('content')
    <p>Dear HTE Representative,</p>
    
    @if($hoursRemaining && $hoursRemaining < 24)
        <div class="warning">
            <h3>⚠️ Deadline Approaching!</h3>
            @if($isPlacement)
                <p>Your student placements deadline is in {{ $hoursRemaining }} hours ({{ $deadlineDate }}).</p>
                <p><strong>Please complete your placements soon to avoid missing the deadline.</strong></p>
            @else
                <p>Your HTE assessment form deadline is in {{ $hoursRemaining }} hours ({{ $deadlineDate }}).</p>
                <p><strong>Please submit your form soon to avoid missing the deadline.</strong></p>
            @endif
        </div>
    @elseif($daysRemaining === 1)
        <div class="warning">
            <h3>⚠️ Deadline Tomorrow!</h3>
            @if($isPlacement)
                <p>Your student placements deadline is tomorrow ({{ $deadlineDate }}).</p>
                <p><strong>Please complete your student placements immediately to avoid missing the deadline.</strong></p>
            @else
                <p>Your HTE assessment form deadline is tomorrow ({{ $deadlineDate }}).</p>
                <p><strong>Please submit your assessment form immediately to avoid missing the deadline.</strong></p>
            @endif
        </div>
    @elseif($daysRemaining === 3)
        <div class="info">
            <h3>📅 Deadline in 3 Days</h3>
            @if($isPlacement)
                <p>Your student placements deadline is in 3 days ({{ $deadlineDate }}).</p>
                <p>Please prepare and complete your student placements soon.</p>
            @else
                <p>Your HTE assessment form deadline is in 3 days ({{ $deadlineDate }}).</p>
                <p>Please prepare and submit your assessment form soon.</p>
            @endif
        </div>
    @elseif($daysRemaining === 5)
        <div class="info">
            <h3>📅 Deadline in 5 Days</h3>
            @if($isPlacement)
                <p>Your student placements deadline is in 5 days ({{ $deadlineDate }}).</p>
                <p>Please start preparing your student placements.</p>
            @else
                <p>Your HTE assessment form deadline is in 5 days ({{ $deadlineDate }}).</p>
                <p>Please start preparing your assessment form.</p>
            @endif
        </div>
    @else
        <div class="info">
            <h3>📅 Deadline Reminder</h3>
            @if($isPlacement)
                <p>Your student placements deadline has been updated to {{ $deadlineDate }}.</p>
                <p>Please complete your student placements before the deadline.</p>
            @else
                <p>Your HTE assessment form deadline has been updated to {{ $deadlineDate }}.</p>
                <p>Please submit your assessment form before the deadline.</p>
            @endif
        </div>
    @endif
    
    <div class="login-section">
        <h3>🚀 Take Action Now</h3>
        <p>Click the button below to access the required section:</p>
        @if($isPlacement)
            <a href="{{ url('/hte/endorsement-table') }}" class="button">Complete Placements</a>
        @else
            <a href="{{ url('/form') }}" class="button">Submit Form</a>
        @endif
    </div>
    
    @if($daysRemaining === 1 || ($hoursRemaining && $hoursRemaining < 24))
        <div class="warning">
            <h4>⚠️ Urgent Action Required:</h4>
            <ul>
                <li>This is your final reminder before the deadline</li>
                <li>Late submissions may not be accepted</li>
                <li>Complete all required sections thoroughly</li>
            </ul>
        </div>
    @else
        <div class="info">
            <h4>📋 Guidelines:</h4>
            <ul>
                @if($isPlacement)
                    <li>Review all student applications carefully</li>
                    <li>Ensure placements match student qualifications</li>
                    <li>Complete all required documentation</li>
                @else
                    <li>Fill out all required fields completely</li>
                    <li>Provide accurate company information</li>
                    <li>Attach any required documents</li>
                @endif
                <li>Save your progress regularly</li>
            </ul>
        </div>
    @endif
    
    <p>If you have any questions or need assistance, please contact the BULSU InternConnect support team.</p>
@endsection
