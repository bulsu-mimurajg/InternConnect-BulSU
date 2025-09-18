<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Deadline extends Model
{
    protected $fillable = [
        'title',
        'category',
        'start_date',
        'end_date',
        'status',
    ];

    public $timestamps = true;

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($deadline) {
            // Automatically set status based on dates
            $now = Carbon::now();
            if ($deadline->end_date < $now) {
                $deadline->status = 'expired';
            } else {
                $deadline->status = 'active';
            }
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date > Carbon::now();
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->end_date < Carbon::now();
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayName(): string
    {
        return match($this->category) {
            'student_verification' => 'Student Verification',
            'student_assessment_form' => 'Student Assessment Form',
            'hte_assessment_form' => 'HTE Assessment Form',
            'skill_assessment_form' => 'Skill Assessment Form',
            default => $this->category,
        };
    }

    /**
     * Check if deadline is currently active for a specific category
     */
    public static function isActiveForCategory(string $category): bool
    {
        return self::where('category', $category)
            ->where('status', 'active')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>', Carbon::now())
            ->exists();
    }

    /**
     * Get active deadline for a specific category
     */
    public static function getActiveForCategory(string $category): ?self
    {
        return self::where('category', $category)
            ->where('status', 'active')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>', Carbon::now())
            ->first();
    }

    /**
     * Get all expired deadlines
     */
    public static function getExpired(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('status', 'expired')
            ->orWhere('end_date', '<', Carbon::now())
            ->orderBy('end_date', 'desc')
            ->get();
    }

    /**
     * Get all active deadlines
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->orderBy('end_date', 'asc')
            ->get();
    }
}
