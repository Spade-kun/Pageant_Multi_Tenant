<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'interval',
        'max_events',
        'max_contestants',
        'max_categories',
        'max_judges',
        'description',
        'analytics',
        'support_priority',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'max_events' => 'integer',
        'max_contestants' => 'integer',
        'max_categories' => 'integer',
        'max_judges' => 'integer',
        'analytics' => 'boolean',
        'support_priority' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function getFeaturesAttribute()
    {
        return [
            'max_events' => $this->max_events,
            'max_contestants' => $this->max_contestants,
            'max_categories' => $this->max_categories,
            'max_judges' => $this->max_judges,
            'analytics' => $this->analytics,
            'support_priority' => $this->support_priority
        ];
    }
} 