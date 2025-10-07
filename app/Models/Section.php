<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Section extends Model
{
    protected $primaryKey = 'section_id';
    
    protected $fillable = [
        'section_name',
        'status'
    ];

    /**
     * Get the valid status values
     */
    public static function getValidStatuses(): array
    {
        return ['active', 'archived'];
    }

    /**
     * Get the requests for this section.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'section_id');
    }

    /**
     * Get the students in this section.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'section_id');
    }

    /**
     * Get the advisers assigned to this section.
     */
    public function advisers(): BelongsToMany
    {
        return $this->belongsToMany(Adviser::class, 'adviser_section', 'section_id', 'adviser_id', 'section_id', 'id');
    }
}
