<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'primary_contact_id',
        'start_date',
        'end_date',
        'rent_amount_cents',
        'deposit_amount_cents',
        'frequency',
        'late_fee_rules',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'late_fee_rules' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lease) {
            // Set status based on dates
            if ($lease->start_date->isFuture()) {
                $lease->status = 'pending';
            } elseif ($lease->start_date->isPast() && $lease->end_date->isFuture()) {
                $lease->status = 'active';
            } elseif ($lease->end_date->isPast()) {
                $lease->status = 'expired';
            }
        });
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function primaryContact()
    {
        return $this->belongsTo(Contact::class, 'primary_contact_id');
    }

    public function charges()
    {
        return $this->hasMany(LeaseCharge::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
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