<?php

namespace App\Policies;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        if ($reservation->getReservationStatusAttribute() === ReservationStatus::FINISHED ||
            $reservation->getReservationStatusAttribute() === ReservationStatus::CANCELED) {
            return false;
        } elseif ($reservation->user_id !== $user->id || $reservation->assigned_user_id !== $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        if ($reservation->getReservationStatusAttribute() === ReservationStatus::FINISHED) {
            return false;
        } elseif ($reservation->user_id !== $user->id || $reservation->assigned_user_id !== $user->id) {
            return false;
        }

        return true;
    }
}
