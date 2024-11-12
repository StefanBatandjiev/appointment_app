<?php

namespace App\Filament\App\Widgets;

use App\Enums\ReservationStatus;
use App\Filament\App\Resources\ReservationResource\Components\ReservationTable;
use App\Models\Reservation;
use Filament\Facades\Filament;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingReservations extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $table->heading(__('Upcoming Reservations'));
        return ReservationTable::make($table
                ->query(Reservation::query()
                    ->where('tenant_id', Filament::getTenant()->id)
                    ->where('start_time', '>=', now())
                    ->where('status', '!=', ReservationStatus::CANCELED)
                )->defaultPaginationPageOption(5));
    }
}
