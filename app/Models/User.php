<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function currentOrganization()
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    public function hasOrganizationRole($roles): bool
{
    if (!$this->current_organization_id) {
        return false;
    }
    
    $roles = is_array($roles) ? $roles : [$roles];
    
    return $this->organizations()
        ->where('organization_id', $this->current_organization_id)
        ->whereIn('organization_user.role', $roles)
        ->exists();
}

    public function isOwnerOrManager()
    {
        return $this->hasOrganizationRole(['owner', 'manager']);
    }
}