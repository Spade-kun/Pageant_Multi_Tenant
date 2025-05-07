<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::all();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(StorePlanRequest $request)
    {
        $validated = $request->validated();

        $plan = Plan::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'interval' => $validated['interval'],
            'max_events' => $validated['max_events'],
            'max_contestants' => $validated['max_contestants'],
            'max_categories' => $validated['max_categories'],
            'max_judges' => $validated['max_judges'],
            'description' => $validated['description'],
            'analytics' => $request->boolean('analytics'),
            'support_priority' => $request->boolean('support_priority'),
            'dashboard_access' => $request->boolean('dashboard_access', true),
            'user_management' => $request->boolean('user_management', true),
            'subscription_management' => $request->boolean('subscription_management', true),
            'pageant_management' => $request->boolean('pageant_management'),
            'reports_module' => $request->boolean('reports_module'),
            'is_active' => true,
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function show(Plan $plan)
    {
        return view('admin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $validated = $request->validated();

        $plan->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'interval' => $validated['interval'],
            'max_events' => $validated['max_events'],
            'max_contestants' => $validated['max_contestants'],
            'max_categories' => $validated['max_categories'],
            'max_judges' => $validated['max_judges'],
            'description' => $validated['description'],
            'analytics' => $request->boolean('analytics'),
            'support_priority' => $request->boolean('support_priority'),
            'is_active' => $request->boolean('is_active'),
            'dashboard_access' => $request->boolean('dashboard_access', true),
            'user_management' => $request->boolean('user_management', true),
            'subscription_management' => $request->boolean('subscription_management', true),
            'pageant_management' => $request->boolean('pageant_management'),
            'reports_module' => $request->boolean('reports_module'),
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->tenants()->count() > 0) {
            return redirect()->route('admin.plans.index')
                ->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
} 