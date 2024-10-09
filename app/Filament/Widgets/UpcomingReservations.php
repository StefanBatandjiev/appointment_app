<?php

namespace App\Filament\Widgets;

use App\Enums\ReservationStatus;
use App\Filament\Components\ReservationTable;
use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingReservations extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return ReservationTable::make($table
                ->query(Reservation::query()
                    ->where('start_time', '>=', now())
                    ->where('status', '!=', ReservationStatus::CANCELED)
                )->defaultPaginationPageOption(5));
    }
}
