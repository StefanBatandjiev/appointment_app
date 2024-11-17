<?php

namespace App\Traits;

use Filament\Facades\Filament;

trait FilterByTenant
{
    protected static function bootFilterByTenant()
    {
        static::creating(function ($model) {
                $tenant = Filament::getTenant();
                if ($tenant) {
                    $model->tenant_id = $tenant->id;
                }
        });
    }
}
