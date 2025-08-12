<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->morphs('documentable'); // entity_type, entity_id
            $table->string('filename');
            $table->string('path'); // S3 path
            $table->string('mime_type');
            $table->bigInteger('size'); // bytes
            $table->jsonb('tags')->nullable();
            $table->jsonb('ai_summary')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['organization_id', 'documentable_type', 'documentable_id'], 'org_documentable_index');
            $table->index(['organization_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};