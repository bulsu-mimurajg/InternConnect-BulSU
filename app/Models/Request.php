<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model
{
    protected $primaryKey = 'request_id';
    
    protected $fillable = [
        'stud_num',
        'section_id'
    ];

    /**
     * Get the section that owns the request.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
