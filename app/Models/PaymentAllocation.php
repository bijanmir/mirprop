<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'lease_charge_id',
        'amount_cents',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function leaseCharge()
    {
        return $this->belongsTo(LeaseCharge::class);
    }

    public function getAmountAttribute()
    {
        return $this->amount_cents / 100;
    }
}