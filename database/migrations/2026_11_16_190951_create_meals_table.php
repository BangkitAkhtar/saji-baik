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
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('category');
            $table->unsignedInteger('price');
            $table->unsignedInteger('stock');
            $table->date('available_date');
            $table->string('pickup_start')->default('11:00');
            $table->string('pickup_end')->default('19:00');
            $table->json('components');
            $table->string('allergens');
            $table->unsignedInteger('calories');
            $table->unsignedInteger('protein');
            $table->string('image')->default('nasi-ayam.jpg');
            $table->boolean('is_active')->default(true);
            $table->index(['is_active', 'available_date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
