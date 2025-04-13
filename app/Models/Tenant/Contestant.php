<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contestant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'age',
        'gender',
        'representing',
        'bio',
        'photo',
        'score'
    ];

    // Relationship with scores
    public function scores()
    {
        return $this->hasMany(Score::class);
    }
} 