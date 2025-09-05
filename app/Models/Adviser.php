<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adviser extends Model
{
    protected $fillable = [
        'adviser_fname',
        'adviser_lname',
        'is_active',
        'section_id',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'section_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }
}
