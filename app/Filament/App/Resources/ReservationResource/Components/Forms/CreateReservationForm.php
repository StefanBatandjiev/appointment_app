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
                    ReservationInputs::selectMachine()->columnSpan(['default' => 3, 'md' => 2]),
                    ReservationInputs::selectAssignedUser()->columnSpan(['default' => 3, 'md' => 1]),
                    ReservationInputs::selectMultipleOperations(true)->columnSpan(3),
                    ReservationInputs::selectDate()->columnSpan(3),
                    ReservationInputs::selectStartTime()->columnSpan(['default' => 3, 'md' => 1]),
                    ReservationInputs::selectDuration()->columnSpan(['default' => 3, 'md' => 1]),
                    ReservationInputs::selectBreakDuration()->columnSpan(['default' => 3, 'md' => 1])
                ])->columnSpan(2),
        ];
    }
}
