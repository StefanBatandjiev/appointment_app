<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\FilterByTenant;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telephone',
        'is_admin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function is_admin(): bool
    {
        return $this->is_admin;
    }

    public function created_reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    public function assigned_reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'assigned_user_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)->withPivot('id');
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->tenants->contains($tenant);
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->tenants;
    }

    protected static function booted()
    {
        static::addGlobalScope('tenant', function ($builder) {
            $tenant = Filament::getTenant();

            if ($tenant) {
                $builder->whereHas('tenants', function (Builder $query) use ($tenant) {
                    $query->where('tenants.id', $tenant->id);
                });
            }
        });
    }
}
