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
        // Create users table first if it doesn't exist
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('user');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Create user profiles table
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('age')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
        
        // Pageant contestants table
        Schema::create('contestants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('age');
            $table->string('gender');
            $table->string('representing');
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('registration_date')->default(now());
            $table->timestamps();
        });
        
        // Pageant categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('percentage', 5, 2); // e.g., 25.00 for 25%
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
        
        // Pageant events table
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('upcoming');
            $table->timestamps();
        });
        
        // Pageant judges table
        Schema::create('judges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('specialty')->nullable();
            $table->timestamps();
        });
        
        // Pageant criteria table (renamed from criteria to criterias)
        Schema::create('criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('weight');
            $table->timestamps();
        });
        
        // Pageant scores table with foreign key constraints
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contestant_id');
            $table->unsignedBigInteger('judge_id');
            $table->unsignedBigInteger('criteria_id');
            $table->unsignedBigInteger('event_id');
            $table->decimal('score', 5, 2);
            $table->timestamps();
        });

        // Add foreign key constraints after all tables are created
        Schema::table('scores', function (Blueprint $table) {
            $table->foreign('contestant_id')->references('id')->on('contestants')->onDelete('cascade');
            $table->foreign('judge_id')->references('id')->on('judges')->onDelete('cascade');
            $table->foreign('criteria_id')->references('id')->on('criterias')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
        Schema::dropIfExists('criterias');
        Schema::dropIfExists('judges');
        Schema::dropIfExists('events');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('contestants');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('users');
    }
}; 