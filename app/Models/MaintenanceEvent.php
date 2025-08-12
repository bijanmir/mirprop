<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'actor_id',
        'type',
        'notes',
        'cost_cents',
        'attachments',
        'meta',
    ];

    protected $casts = [
        'attachments' => 'array',
        'meta' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(MaintenanceTicket::class, 'ticket_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getCostAttribute()
    {
        return $this->cost_cents ? $this->cost_cents / 100 : null;
    }

    public function setCostAttribute($value)
    {
        $this->cost_cents = $value ? $value * 100 : null;
    }
}