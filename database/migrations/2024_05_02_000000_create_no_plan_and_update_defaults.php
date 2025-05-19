<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, create a "No Plan" record in the plans table with ID 0
        DB::table('plans')->insert([
            'id' => 0,
            'name' => 'No Plan',
            'price' => 0,
            'interval' => 'monthly', 
            'max_events' => 0,
            'max_contestants' => 0,
            'max_categories' => 0,
            'max_judges' => 0,
            'description' => 'Default plan with basic access only',
            'analytics' => false,
            'support_priority' => false,
            'is_active' => true,
            'dashboard_access' => true,
            'user_management' => true,
            'subscription_management' => true,
            'pageant_management' => false,
            'reports_module' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update existing NULL values in the tenants table to 0
        DB::table('tenants')
            ->whereNull('plan_id')
            ->update(['plan_id' => 0]);

        // We don't need to change schema structure since we're keeping it nullable
        // This keeps the foreign key constraint working properly
        // Just making sure all NULL values are changed to 0
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set the "No Plan" (0) records back to NULL
        DB::table('tenants')
            ->where('plan_id', 0)
            ->update(['plan_id' => null]);

        // Delete the "No Plan" record
        DB::table('plans')->where('id', 0)->delete();
    }
}; 