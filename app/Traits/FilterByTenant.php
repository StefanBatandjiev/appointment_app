<?php

namespace App\Traits;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

trait FilterByTenant
{
    protected static function bootFilterByTenant()
    {
        if (auth()->check() && !auth()->user()->is_admin()) {
            static::creating(function ($model) {
                $tenant = Filament::getTenant();
                if ($tenant) {
                    $model->tenant_id = $tenant->id;
                }
            });

            static::addGlobalScope('tenant', function (Builder $builder) {
                $tenant = Filament::getTenant();
                if ($tenant) {
                    $builder->where('tenant_id', $tenant->id);
                }
            });
        }
    }
}
