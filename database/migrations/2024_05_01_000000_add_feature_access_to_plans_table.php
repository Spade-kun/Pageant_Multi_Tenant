<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Default features (always available)
            $table->boolean('dashboard_access')->default(true)->after('is_active');
            $table->boolean('user_management')->default(true)->after('dashboard_access');
            $table->boolean('subscription_management')->default(true)->after('user_management');
            
            // Premium features
            $table->boolean('pageant_management')->default(false)->after('subscription_management');
            $table->boolean('reports_module')->default(false)->after('pageant_management');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'dashboard_access',
                'user_management',
                'subscription_management',
                'pageant_management',
                'reports_module'
            ]);
        });
    }
}; 