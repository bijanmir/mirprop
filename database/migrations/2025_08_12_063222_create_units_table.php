<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->string('label'); // e.g., "101", "A", "Suite 200"
            $table->integer('beds')->nullable();
            $table->decimal('baths', 3, 1)->nullable(); // 2.5 baths
            $table->integer('sqft')->nullable();
            $table->bigInteger('rent_amount_cents')->default(0);
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->jsonb('meta')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['organization_id', 'property_id']);
            $table->index(['organization_id', 'status']);
            $table->unique(['property_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};