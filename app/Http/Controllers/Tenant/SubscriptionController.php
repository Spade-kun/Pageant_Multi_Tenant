<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function showPlans()
    {
        $tenant = Tenant::where('slug', request()->route('slug'))->first();
        $trialEndsAt = Carbon::parse($tenant->created_at)->addDays(3);
        $daysLeft = now()->diffInDays($trialEndsAt, false);
        
        if ($daysLeft > 0) {
            session(['trial_days_left' => $daysLeft]);
        }
        
        return view('tenant.subscription.plans');
    }

    public function update(Request $request)
    {
        $tenant = Tenant::where('slug', $request->route('slug'))->first();
        
        // Validate plan
        $request->validate([
            'plan' => 'required|in:30_days,monthly,yearly'
        ]);

        // Calculate expiration date based on plan
        $expiresAt = now();
        switch ($request->plan) {
            case '30_days':
                $expiresAt = $expiresAt->addDays(30);
                $planName = 'Basic (30 Days)';
                break;
            case 'monthly':
                $expiresAt = $expiresAt->addMonth();
                $planName = 'Standard (Monthly)';
                break;
            case 'yearly':
                $expiresAt = $expiresAt->addYear();
                $planName = 'Premium (Yearly)';
                break;
        }

        // Update tenant subscription
        $tenant->update([
            'subscription_plan' => $request->plan,
            'subscription_ends_at' => $expiresAt,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', "Successfully updated to {$planName} plan. Your subscription is valid until " . $expiresAt->format('M d, Y'));
    }
} 