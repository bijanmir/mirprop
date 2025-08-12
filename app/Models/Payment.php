<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'lease_id',
        'contact_id',
        'amount_cents',
        'method',
        'processor_id',
        'status',
        'failure_reason',
        'failure_message',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function getAmountAttribute()
    {
        return $this->amount_cents / 100;
    }

    public function setAmountAttribute($value)
    {
        $this->amount_cents = $value * 100;
    }
}