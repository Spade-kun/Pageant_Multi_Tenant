<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $connection = 'tenant';
    
    protected $fillable = [
        'name',
        'description',
        'date',
        'time',
        'venue',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime',
    ];
} 