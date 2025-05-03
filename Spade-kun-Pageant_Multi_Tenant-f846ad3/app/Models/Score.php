<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'contestant_id',
        'judge_id',
        'category_id',
        'event_id',
        'raw_score',
        'weighted_score',
        'criteria_scores',
        'comments'
    ];

    protected $casts = [
        'criteria_scores' => 'array',
        'raw_score' => 'decimal:2',
        'weighted_score' => 'decimal:2'
    ];

    public function contestant()
    {
        return $this->belongsTo(Contestant::class);
    }

    public function judge()
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
} 