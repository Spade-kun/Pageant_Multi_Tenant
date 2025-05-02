<?php

namespace App\Models;

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

    protected $casts = [
        'score' => 'float',
        'age' => 'integer',
    ];
} 