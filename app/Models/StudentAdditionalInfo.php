<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAdditionalInfo extends Model
{
    protected $fillable = [
        'additional_info_id',
        'student_id',
        'info',
    ];

    public function additionalInfo(): BelongsTo
    {
        return $this->belongsTo(AdditionalInfo::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
