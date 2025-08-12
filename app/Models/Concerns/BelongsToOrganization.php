<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;

trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization()
    {
        static::addGlobalScope(new OrganizationScope);
        
        static::creating(function ($model) {
            if (!$model->organization_id && auth()->check()) {
                $model->organization_id = auth()->user()->current_organization_id;
            }
        });
    }
    
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}