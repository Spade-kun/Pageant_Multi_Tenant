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
        // Add foreign key from tenants to tenant_users
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('owner_id')
                  ->references('id')
                  ->on('tenant_users')
                  ->onDelete('cascade');
        });

        // Add foreign key from tenant_users to tenants
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key from tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
        });

        // Remove foreign key from tenant_users
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });
    }
}; 