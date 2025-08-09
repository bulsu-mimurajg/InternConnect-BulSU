<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HTE extends Model
{
    /** @use HasFactory<\Database\Factories\HTEFactory> */
    use HasFactory;

    protected $table = 'HTE';

    protected $fillable = [
        'user_id',
        'company_name',
        'company_address',
        'company_email',
        'cperson_fname',
        'cperson_lname',
        'cperson_position',
        'cperson_contactnum',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the HTE.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the internships for the HTE.
     */
    public function internships(): HasMany
    {
        return $this->hasMany(Internship::class);
    }

    /**
     * Get the full name of the contact person.
     */
    public function getContactPersonFullNameAttribute(): string
    {
        return $this->cperson_fname . ' ' . $this->cperson_lname;
    }
}
