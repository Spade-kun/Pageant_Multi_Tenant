<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('interval'); // monthly, yearly
            $table->integer('max_events')->default(1);
            $table->integer('max_contestants')->default(50);
            $table->integer('max_categories')->default(5);
            $table->integer('max_judges')->default(5);
            $table->text('description')->nullable();
            $table->boolean('analytics')->default(false);
            $table->boolean('support_priority')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plans');
    }
}; 