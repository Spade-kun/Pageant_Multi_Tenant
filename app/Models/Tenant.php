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
     * Get the owner of the tenant.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
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

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function planRequests()
    {
        return $this->hasMany(PlanRequest::class);
    }
} 