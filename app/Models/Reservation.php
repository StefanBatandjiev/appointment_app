<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'client_id', 'machine_id', 'operation_id', 'start_time', 'end_time', 'break_time'];

    protected $dates = ['start_time', 'end_time'. 'break_time'];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
