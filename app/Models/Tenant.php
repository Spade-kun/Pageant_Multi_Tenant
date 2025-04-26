<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pageant_name',
        'slug',
        'email',
        'phone',
        'address',
        'subscription_plan_id',
        'subscription_expires_at',
        'is_active',
        'name',
        'domain',
        'database',
        'plan_id',
        'status',
        'rejection_reason',
        'database_name',
        'owner_id'
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($tenant) {
            // Set default plan_id to 0 (No Plan) if not set
            if (is_null($tenant->plan_id)) {
                $tenant->plan_id = 0;
            }
        });
    }

    /**
     * Get the owner of the tenant.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'owner_id');
    }

    /**
     * Get the users of the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    /**
     * Determine if the tenant is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Determine if the tenant is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Determine if the tenant is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get the tenant's plan.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Check if tenant has only the default "No Plan".
     */
    public function hasNoPlan(): bool
    {
        return $this->plan_id === 0;
    }

    /**
     * Check if tenant has a premium plan (not the default No Plan).
     */
    public function hasPremiumPlan(): bool
    {
        return $this->plan_id > 0;
    }

    public function planRequests()
    {
        return $this->hasMany(PlanRequest::class);
    }
} 