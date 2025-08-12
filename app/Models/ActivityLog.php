<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'actor_id',
        'entity_type',
        'entity_id',
        'action',
        'diff',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'diff' => 'array',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function entity()
    {
        return $this->morphTo();
    }
}