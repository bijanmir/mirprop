<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'documentable_type',
        'documentable_id',
        'filename',
        'path',
        'mime_type',
        'size',
        'tags',
        'ai_summary',
    ];

    protected $casts = [
        'tags' => 'array',
        'ai_summary' => 'array',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function getHumanReadableSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}