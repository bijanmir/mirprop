<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaseCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'lease_id',
        'type',
        'amount_cents',
        'description',
        'due_date',
        'balance_cents',
        'is_recurring',
        'day_of_month',
        'meta',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_recurring' => 'boolean',
        'meta' => 'array',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function paymentAllocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function getAmountAttribute()
    {
        return $this->amount_cents / 100;
    }

    public function getBalanceAttribute()
    {
        return $this->balance_cents / 100;
    }

    public function isPaid()
    {
        return $this->balance_cents === 0;
    }
}