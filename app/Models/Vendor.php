<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'contact_id',
        'services',
        'w9_url',
        'is_active',
    ];

    protected $casts = [
        'services' => 'array',
        'is_active' => 'boolean',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedTickets()
    {
        return $this->hasMany(MaintenanceTicket::class, 'assigned_vendor_id');
    }
}