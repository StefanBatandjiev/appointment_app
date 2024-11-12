<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Traits\FilterByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory, FilterByTenant;

    protected $fillable = ['name', 'description', 'tenant_id'];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(Operation::class, 'machine_operation');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted()
    {
        self::addGlobalScope(new TenantScope);
    }
}
