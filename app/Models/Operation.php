<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = ['machine_id', 'name', 'description', 'price', 'color'];

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'operation_reservation', 'operation_id', 'reservation_id');
    }

    public function machines(): BelongsToMany
    {
        return $this->belongsToMany(Machine::class, 'machine_operation');
    }
}
