<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'settings',
        'billing_info',
    ];

    protected $casts = [
        'settings' => 'array',
        'billing_info' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            if (!$organization->slug) {
                $organization->slug = Str::slug($organization->name);
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}