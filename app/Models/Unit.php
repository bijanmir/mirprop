<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'property_id',
        'label',
        'beds',
        'baths',
        'sqft',
        'rent_amount_cents',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'baths' => 'decimal:1',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease()
    {
        return $this->hasOne(Lease::class)->where('status', 'active');
    }

    public function maintenanceTickets()
    {
        return $this->hasMany(MaintenanceTicket::class);
    }

    public function getFullIdentifierAttribute()
    {
        return $this->property->name . ' - ' . $this->label;
    }

    public function getRentAmountAttribute()
    {
        return $this->rent_amount_cents / 100;
    }

    public function setRentAmountAttribute($value)
    {
        $this->rent_amount_cents = $value * 100;
    }
}