<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Traits\FilterByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operation extends Model
{
    use HasFactory, FilterByTenant;

    protected $fillable = ['tenant_id', 'machine_id', 'name', 'description', 'price', 'color'];

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'operation_reservation', 'operation_id', 'reservation_id')
            ->withPivot('price');
    }

    public function machines(): BelongsToMany
    {
        return $this->belongsToMany(Machine::class, 'machine_operation');
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
