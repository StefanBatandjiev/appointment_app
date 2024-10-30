<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Reservation extends Model
{
    use HasFactory;
    use HasTranslations;

    public $translatable = [
        'start_time',
        'end_time',
        'break_time',
        'status'
    ];


    protected $fillable = ['user_id', 'client_id', 'machine_id', 'operation_id', 'assigned_user_id', 'start_time', 'end_time', 'break_time', 'status'];

    protected $dates = ['start_time', 'end_time', 'break_time'];

    protected $casts = ['status' => ReservationStatus::class];

    public function getTotalPriceAttribute()
    {
        return $this->operations->sum('price');
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
        return $this->belongsToMany(Operation::class, 'operation_reservation', 'reservation_id', 'operation_id');
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
}
