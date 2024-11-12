<?php

namespace App\Filament\App\Resources\MachineResource\Widgets;

use App\Filament\App\Resources\ReservationResource\Components\ReservationFilters;
use App\Filament\App\Resources\ReservationResource\Components\ReservationTable;
use App\Models\Machine;
use App\Models\Reservation;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class MachineReservations extends BaseWidget
{
    public ?Machine $record = null;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('Machine Reservations');
    }

    public function table(Table $table): Table
    {
        $table
            ->query(Reservation::query()->where('machine_id', $this->record->id))->defaultPaginationPageOption(5)
            ->filters([
                ReservationFilters::status(),
                ReservationFilters::fromDate(),
                ReservationFilters::toDate(),
                ReservationFilters::client(),
                ReservationFilters::operations(),
                ReservationFilters::assigned_user()
            ]);
        return ReservationTable::make($table, false, true);
    }
}
