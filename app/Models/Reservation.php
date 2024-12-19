<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Models\Scopes\TenantScope;
use App\Traits\FilterByTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Reservation extends Model
{
    use HasFactory, FilterByTenant;
    protected $fillable = ['tenant_id', 'user_id', 'client_id', 'machine_id', 'operation_id', 'assigned_user_id', 'start_time', 'end_time', 'break_time', 'status', 'final_price', 'discount'];

    protected $dates = ['start_time', 'end_time', 'break_time'];

    protected $casts = ['status' => ReservationStatus::class];

    public function getTotalPriceAttribute()
    {
        if ($this->status === ReservationStatus::FINISHED && $this->final_price !== null) {
            return $this->final_price;
        }

        return $this->operations->sum('price');
    }

    public function getDiscountedPriceAttribute(): float
    {
        $price = $this->getTotalPriceAttribute();
        $discount = $this->discount ?? 0;

        return ceil($price - ($price * $discount / 100));
    }

    public function getReservationStatusAttribute()
    {
        $currentDate = now();

        $start_time = Carbon::parse($this->start_time);
        $end_time = Carbon::parse($this->end_time);

        if ($this->status === ReservationStatus::CANCELED || $this->status === ReservationStatus::FINISHED) {
            return $this->status;
        }

        if ($currentDate->between($start_time, $end_time)) {
            return ReservationStatus::ONGOING;
        } elseif ($currentDate->lessThan($start_time)) {
            return ReservationStatus::SCHEDULED;
        } elseif ($currentDate->greaterThan($end_time)) {
            return ReservationStatus::PENDING_FINISH;
        }

        return $this->status;
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(Operation::class, 'operation_reservation', 'reservation_id', 'operation_id')
            ->withPivot('price');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assigned_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted()
    {
        self::addGlobalScope(new TenantScope);

        static::updating(function (Reservation $reservation) {
            if ($reservation->isDirty('status') && $reservation->status === ReservationStatus::FINISHED) {
                DB::transaction(function () use ($reservation) {
                    foreach ($reservation->operations as $operation) {
                        $reservation->operations()->updateExistingPivot($operation->id, ['price' => $operation->price]);
                    }

                    $reservation->final_price = $reservation->operations->sum('price');
                });
            }
        });
    }
}
