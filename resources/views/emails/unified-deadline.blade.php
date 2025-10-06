@extends('emails.layout')

@section('content')
    <p style="font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 15px;">Hello, {{ $userDisplayName }}!</p>
    <p style="margin-bottom: 20px;">{{ $urgencyLevel['message'] }}</p>
    
    <div class="highlight">
        <h3>{{ $deadlineName }}</h3>
        <div class="credential-item">
            <strong>Category:</strong> {{ $categoryDisplay }}
        </div>
        <div class="credential-item">
            <strong>Deadline:</strong> {{ $deadlineDate->format('F d, Y \a\t g:i A') }}
        </div>
        <div class="credential-item">
            <strong>Status:</strong> {{ $timeRemainingText }}
        </div>
        @if($daysRemaining !== null)
            <div class="credential-item">
                <strong>Time Remaining:</strong> 
                @if($daysRemaining == 1)
                    Due Tomorrow!
                @elseif($daysRemaining == 0)
                    Due Today!
                @else
                    {{ $daysRemaining }} Days Remaining
                @endif
            </div>
        @elseif($hoursRemaining !== null)
            <div class="credential-item">
                <strong>Time Remaining:</strong> 
                @if($hoursRemaining == 1)
                    1 Hour Remaining!
                @else
                    {{ $hoursRemaining }} Hours Remaining!
                @endif
            </div>
        @endif
    </div>
    
    <div class="info">
        <h3>Required Action</h3>
        <p>{{ $actionText }}</p>
        @if($roleSpecificContent)
            <p><strong>Role-Specific Information:</strong> {{ $roleSpecificContent }}</p>
        @endif
    </div>
    
    <div class="button-container">
        <a href="{{ $actionUrl }}" class="button">View Details & Take Action</a>
    </div>
    
    <div class="warning">
        <h4>Important</h4>
        <ul>
            <li>Please complete the required actions before the deadline</li>
            <li>Contact the system administrator if you have any questions</li>
        </ul>
    </div>
@endsection