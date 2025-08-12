<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['rent', 'fee', 'credit', 'deposit']);
            $table->bigInteger('amount_cents');
            $table->string('description')->nullable();
            $table->date('due_date');
            $table->bigInteger('balance_cents'); // Remaining balance
            $table->boolean('is_recurring')->default(false);
            $table->integer('day_of_month')->nullable(); // For recurring charges
            $table->jsonb('meta')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['lease_id', 'type']);
            $table->index(['lease_id', 'due_date']);
            $table->index(['lease_id', 'balance_cents']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_charges');
    }
};