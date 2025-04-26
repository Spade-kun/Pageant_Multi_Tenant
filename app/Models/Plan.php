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
        'is_active',
        'dashboard_access',
        'user_management',
        'subscription_management',
        'pageant_management',
        'reports_module'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'max_events' => 'integer',
        'max_contestants' => 'integer',
        'max_categories' => 'integer',
        'max_judges' => 'integer',
        'analytics' => 'boolean',
        'support_priority' => 'boolean',
        'is_active' => 'boolean',
        'dashboard_access' => 'boolean',
        'user_management' => 'boolean',
        'subscription_management' => 'boolean',
        'pageant_management' => 'boolean',
        'reports_module' => 'boolean'
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
            'support_priority' => $this->support_priority,
            'dashboard_access' => $this->dashboard_access,
            'user_management' => $this->user_management,
            'subscription_management' => $this->subscription_management,
            'pageant_management' => $this->pageant_management,
            'reports_module' => $this->reports_module
        ];
    }
} 