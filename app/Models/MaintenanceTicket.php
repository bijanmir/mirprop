<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'property_id',
        'unit_id',
        'contact_id',
        'assigned_vendor_id',
        'title',
        'description',
        'priority',
        'status',
        'sla_due_at',
        'completed_at',
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Set SLA based on priority
            if (!$ticket->sla_due_at) {
                $hours = match($ticket->priority) {
                    'emergency' => 24,
                    'high' => 48,
                    'medium' => 72,
                    'low' => 120,
                    default => 72,
                };
                $ticket->sla_due_at = now()->addHours($hours);
            }
        });
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedVendor()
    {
        return $this->belongsTo(Vendor::class, 'assigned_vendor_id');
    }

    public function events()
    {
        return $this->hasMany(MaintenanceEvent::class, 'ticket_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}