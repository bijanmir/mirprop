<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'type',
        'name',
        'email',
        'phone',
        'address',
        'meta',
    ];

    protected $casts = [
        'address' => 'array',
        'meta' => 'array',
    ];

    public function leases()
    {
        return $this->hasMany(Lease::class, 'primary_contact_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function maintenanceTickets()
    {
        return $this->hasMany(MaintenanceTicket::class);
    }

    public function isVendor()
    {
        return $this->type === 'vendor' || $this->vendor()->exists();
    }
}