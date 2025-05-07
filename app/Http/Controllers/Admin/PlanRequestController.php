<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTenantPlanRequest;
use App\Models\PlanRequest;
use Illuminate\Http\Request;

class PlanRequestController extends Controller
{
    public function index()
    {
        $requests = PlanRequest::with(['tenant' => function($query) {
            $query->with(['users' => function($q) {
                $q->where('role', 'owner');
            }]);
        }, 'plan'])->latest()->get();
        
        return view('admin.requests.index', compact('requests'));
    }

    public function show(PlanRequest $request)
    {
        $request->load(['tenant' => function($query) {
            $query->with(['users' => function($q) {
                $q->where('role', 'owner');
            }]);
        }, 'plan']);
        
        return view('admin.requests.show', compact('request'));
    }

    public function approve(PlanRequest $request)
    {
        // Update request status
        $request->update(['status' => 'approved']);
        
        // Update tenant's plan
        $request->tenant->update([
            'plan_id' => $request->plan_id
        ]);

        // Send notification or email to tenant about approval (optional)
        // You can implement this later if needed

        return redirect()->route('admin.requests.index')
            ->with('success', 'Plan request approved successfully.');
    }

    public function reject(PlanRequest $request)
    {
        $request->update(['status' => 'rejected']);
        
        return redirect()->route('admin.requests.index')
            ->with('success', 'Plan request rejected successfully.');
    }

    public function showChangePlan($tenant)
    {
        $tenant = \App\Models\Tenant::with('plan')->findOrFail($tenant);
        $plans = \App\Models\Plan::where('is_active', true)->get();
        
        return view('admin.requests.change-plan', compact('tenant', 'plans'));
    }

    public function updatePlan(UpdateTenantPlanRequest $request, $tenant)
    {
        $tenant = \App\Models\Tenant::findOrFail($tenant);
        
        $validated = $request->validated();

        // Update tenant's plan (can be null)
        $tenant->update([
            'plan_id' => $validated['plan_id']
        ]);

        // Only create a plan request record if a plan is selected
        if ($validated['plan_id']) {
            PlanRequest::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $validated['plan_id'],
                'status' => 'approved',
                'notes' => 'Plan changed by admin'
            ]);
        }

        return redirect()->route('admin.requests.index')
            ->with('success', $validated['plan_id'] ? 'Tenant plan updated successfully.' : 'Tenant plan removed successfully.');
    }
} 