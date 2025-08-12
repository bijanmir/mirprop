<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->enum('audience', ['tenants', 'owners', 'all']);
            $table->string('subject');
            $table->text('body');
            $table->timestamp('sent_at')->nullable();
            $table->jsonb('recipients')->nullable(); // Track who received it
            $table->timestamps();
            
            // Indexes
            $table->index(['organization_id', 'sent_at']);
            $table->index(['organization_id', 'audience']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};