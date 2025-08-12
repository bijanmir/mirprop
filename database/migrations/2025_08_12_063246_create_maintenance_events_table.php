<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('maintenance_tickets')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['note', 'status_change', 'assignment', 'cost', 'attachment']);
            $table->text('notes')->nullable();
            $table->bigInteger('cost_cents')->nullable();
            $table->jsonb('attachments')->nullable(); // Array of file paths
            $table->jsonb('meta')->nullable(); // Additional data (old_status, new_status, etc.)
            $table->timestamps();
            
            // Indexes
            $table->index(['ticket_id', 'type']);
            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_events');
    }
};