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
        Schema::create('ui_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('logo_header_color')->default('dark');
            $table->string('navbar_color')->default('white');
            $table->string('sidebar_color')->default('dark');
            $table->string('navbar_position')->default('top');
            $table->string('sidebar_position')->default('left');
            $table->boolean('is_sidebar_collapsed')->default(false);
            $table->boolean('is_navbar_fixed')->default(true);
            $table->boolean('is_sidebar_fixed')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ui_settings');
    }
}; 