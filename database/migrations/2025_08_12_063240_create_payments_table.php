<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('contact_id')->constrained()->restrictOnDelete(); // Payer
            $table->bigInteger('amount_cents');
            $table->enum('method', ['ach', 'card', 'cash', 'check', 'other'])->nullable();
            $table->string('processor_id')->nullable(); // Stripe payment intent ID
            $table->enum('status', ['pending', 'processing', 'succeeded', 'failed'])->default('pending');
            $table->string('failure_reason')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'posted_at']);
            $table->index(['organization_id', 'lease_id']);
            $table->unique('processor_id'); // For idempotency
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};