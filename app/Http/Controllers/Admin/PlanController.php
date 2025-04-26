<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:3_days,15_days,monthly,yearly',
            'max_events' => 'required|integer|min:0',
            'max_contestants' => 'required|integer|min:0',
            'max_categories' => 'required|integer|min:0',
            'max_judges' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'analytics' => 'boolean',
            'support_priority' => 'boolean',
            'dashboard_access' => 'boolean',
            'user_management' => 'boolean',
            'subscription_management' => 'boolean',
            'pageant_management' => 'boolean',
            'reports_module' => 'boolean',
        ]);

        $plan = Plan::create([
            'name' => $request->name,
            'price' => $request->price,
            'interval' => $request->interval,
            'max_events' => $request->max_events,
            'max_contestants' => $request->max_contestants,
            'max_categories' => $request->max_categories,
            'max_judges' => $request->max_judges,
            'description' => $request->description,
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

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:3_days,15_days,monthly,yearly',
            'max_events' => 'required|integer|min:0',
            'max_contestants' => 'required|integer|min:0',
            'max_categories' => 'required|integer|min:0',
            'max_judges' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'analytics' => 'boolean',
            'support_priority' => 'boolean',
            'is_active' => 'boolean',
            'dashboard_access' => 'boolean',
            'user_management' => 'boolean',
            'subscription_management' => 'boolean',
            'pageant_management' => 'boolean',
            'reports_module' => 'boolean',
        ]);

        $plan->update([
            'name' => $request->name,
            'price' => $request->price,
            'interval' => $request->interval,
            'max_events' => $request->max_events,
            'max_contestants' => $request->max_contestants,
            'max_categories' => $request->max_categories,
            'max_judges' => $request->max_judges,
            'description' => $request->description,
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