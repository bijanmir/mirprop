<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('primary_contact_id')->constrained('contacts')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->bigInteger('rent_amount_cents')->default(0);
            $table->bigInteger('deposit_amount_cents')->nullable();
            $table->enum('frequency', ['monthly', 'weekly', 'yearly'])->default('monthly');
            $table->jsonb('late_fee_rules')->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'terminated'])->default('pending');
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'unit_id']);
            $table->index(['organization_id', 'start_date', 'end_date']);
            $table->index(['organization_id', 'primary_contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};