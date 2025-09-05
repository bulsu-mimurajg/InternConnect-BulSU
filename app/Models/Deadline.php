<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Deadline extends Model
{
    protected $fillable = [
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
}
