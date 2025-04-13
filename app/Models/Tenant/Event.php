<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'location'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relationship with scores
    public function scores()
    {
        return $this->hasMany(Score::class);
    }
} 