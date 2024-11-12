<?php

namespace App\Filament\App\Resources\ReservationResource\Components\Forms;

use App\Filament\App\Resources\ReservationResource\Components\Inputs\ReservationInputs;
use Filament\Forms\Components\Grid;

class CreateReservationForm
{

    public static function form(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    ReservationInputs::selectClient(),
                    ReservationInputs::selectMachine(),
                    ReservationInputs::selectAssignedUser(),
                    ReservationInputs::selectMultipleOperations(true),
                    ReservationInputs::selectDate(),
                    ReservationInputs::selectStartTime(),
                    ReservationInputs::selectDuration(),
                    ReservationInputs::selectBreakDuration()
                ])->columnSpan(2),
        ];
    }
}
