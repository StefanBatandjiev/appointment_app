<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = ['machine_id', 'name', 'description', 'color'];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
