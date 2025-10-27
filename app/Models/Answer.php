<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    protected $table = 'answers';

    /**
     * The attributes that are mass assignable.
     * Added `answer_text` so it can be mass assigned.
     *
     * @var array
     */
    protected $fillable = [
        'question_id',
        'answer_text',
        'is_correct',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * The question this answer belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
