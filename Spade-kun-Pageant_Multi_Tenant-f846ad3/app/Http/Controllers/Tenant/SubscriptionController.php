<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanRequest;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SubscriptionController extends Controller
{
    public function showPlans($slug)
    {
        // Get tenant from central database
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        
        // Configure tenant database connection
        $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        // Get plans from central database
        $plans = Plan::where('is_active', true)->get();
        $currentPlan = $tenant->plan;
        
        // Get any pending plan request
        $pendingRequest = PlanRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->first();

        return view('tenant.subscription.plans', compact('plans', 'currentPlan', 'pendingRequest', 'slug', 'tenant'));
    }

    public function requestPlan(Request $request, $slug)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'notes' => 'nullable|string|max:500'
        ]);

        $tenant = Tenant::where('slug', $slug)->firstOrFail();

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