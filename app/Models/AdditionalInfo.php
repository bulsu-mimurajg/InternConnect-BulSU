<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdditionalInfo extends Model
{
    protected $fillable = [
        'info_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function studentAdditionalInfos(): HasMany
    {
        return $this->hasMany(StudentAdditionalInfo::class);
    }
}
