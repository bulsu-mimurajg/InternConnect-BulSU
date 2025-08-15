<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcategoryWeight extends Model
{
    use HasFactory;

    protected $table = 'subcategory_weights';

    protected $fillable = [
        'internship_id',
        'subcategory_id',
        'weight',
    ];

    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id', 'id');
    }
}
