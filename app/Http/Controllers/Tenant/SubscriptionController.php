<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanRequest;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function showPlans()
    {
        $plans = Plan::where('is_active', true)->get();
        $tenant = auth('tenant')->user()->tenant;
        $currentPlan = $tenant->plan;
        
        // Get any pending plan request
        $pendingRequest = PlanRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->first();

        return view('tenant.subscription.plans', compact('plans', 'currentPlan', 'pendingRequest'));
    }

    public function requestPlan(Request $request, $slug)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'notes' => 'nullable|string|max:500'
        ]);

        $tenant = auth()->user()->tenant;

        // Check for existing pending requests
        $existingRequest = PlanRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'You already have a pending plan request.');
        }

        // Create new plan request
        PlanRequest::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $request->plan_id,
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Plan request submitted successfully.');
    }
} 