<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnplacedStudent extends Model
{
    protected $fillable = [
        'student_id',
        'reason',
        'requires_manual_intervention',
        'notes',
        'resolved_at',
    ];

    protected $casts = [
        'requires_manual_intervention' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeRequiresIntervention($query)
    {
        return $query->where('requires_manual_intervention', true);
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }
}
