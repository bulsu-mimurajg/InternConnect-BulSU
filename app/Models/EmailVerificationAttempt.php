<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailVerificationAttempt extends Model
{
    protected $fillable = [
        'email',
        'attempt_count',
        'last_attempt_at',
        'next_attempt_allowed_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'next_attempt_allowed_at' => 'datetime',
    ];

    /**
     * Check if an email can make a verification attempt
     */
    public static function canAttemptVerification(string $email): bool
    {
        $attempt = self::where('email', $email)->first();
        
        if (!$attempt) {
            return true; // First attempt
        }

        // Check if enough time has passed since last attempt
        return now()->gte($attempt->next_attempt_allowed_at);
    }

    /**
     * Record a verification attempt
     */
    public static function recordAttempt(string $email, ?string $ipAddress = null, ?string $userAgent = null): self
    {
        $attempt = self::where('email', $email)->first();
        
        if (!$attempt) {
            // First attempt - 3 minutes interval
            $nextAttemptAt = now()->addMinutes(3);
            
            return self::create([
                'email' => $email,
                'attempt_count' => 1,
                'last_attempt_at' => now(),
                'next_attempt_allowed_at' => $nextAttemptAt,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ]);
        }

        // Increment attempt count and calculate next allowed time
        $attemptCount = $attempt->attempt_count + 1;
        $intervalMinutes = self::getIntervalMinutes($attemptCount);
        $nextAttemptAt = now()->addMinutes($intervalMinutes);

        $attempt->update([
            'attempt_count' => $attemptCount,
            'last_attempt_at' => now(),
            'next_attempt_allowed_at' => $nextAttemptAt,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);

        return $attempt;
    }

    /**
     * Get the interval in minutes based on attempt count
     * 1st retry: 3 minutes
     * 2nd retry: 10 minutes  
     * 3rd retry: 30 minutes
     * 4th retry: 60 minutes
     * 5th+ retry: 120 minutes (2 hours)
     */
    public static function getIntervalMinutes(int $attemptCount): int
    {
        return match ($attemptCount) {
            1 => 3,    // First retry
            2 => 10,   // Second retry
            3 => 30,   // Third retry
            4 => 60,   // Fourth retry
            default => 120 // Fifth and subsequent retries
        };
    }

    /**
     * Get the time remaining until next attempt is allowed
     */
    public static function getTimeUntilNextAttempt(string $email): ?int
    {
        $attempt = self::where('email', $email)->first();
        
        if (!$attempt || now()->gte($attempt->next_attempt_allowed_at)) {
            return null; // Can attempt now
        }

        return now()->diffInSeconds($attempt->next_attempt_allowed_at);
    }

    /**
     * Get formatted time until next attempt
     */
    public static function getFormattedTimeUntilNextAttempt(string $email): ?string
    {
        $seconds = self::getTimeUntilNextAttempt($email);
        
        if (!$seconds) {
            return null;
        }

        $minutes = ceil($seconds / 60);
        
        if ($minutes < 60) {
            return "{$minutes} minute" . ($minutes > 1 ? 's' : '');
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($remainingMinutes === 0) {
            return "{$hours} hour" . ($hours > 1 ? 's' : '');
        }
        
        return "{$hours} hour" . ($hours > 1 ? 's' : '') . " and {$remainingMinutes} minute" . ($remainingMinutes > 1 ? 's' : '');
    }

    /**
     * Reset attempts for an email (useful when verification is successful)
     */
    public static function resetAttempts(string $email): void
    {
        self::where('email', $email)->delete();
    }

    /**
     * Clean up old attempts (older than 24 hours)
     */
    public static function cleanupOldAttempts(): int
    {
        return self::where('last_attempt_at', '<', now()->subHours(24))->delete();
    }
}
