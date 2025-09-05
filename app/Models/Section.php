<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $primaryKey = 'section_id';
    
    protected $fillable = [
        'section_name',
        'status'
    ];

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
}
