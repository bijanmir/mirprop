<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'created_by',
        'audience',
        'subject',
        'body',
        'sent_at',
        'recipients',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'recipients' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSent()
    {
        return $this->sent_at !== null;
    }
}