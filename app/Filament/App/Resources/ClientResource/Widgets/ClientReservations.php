<?php

namespace App\Filament\App\Resources\ClientResource\Widgets;

use App\Filament\App\Resources\ReservationResource\Components\ReservationFilters;
use App\Filament\App\Resources\ReservationResource\Components\ReservationTable;
use App\Models\Client;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class ClientReservations extends BaseWidget
{

    public ?Client $record = null;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('Client Reservations');
    }

    public function table(Table $table): Table
    {
        $table
            ->query($this->record->reservations()->getQuery())->defaultPaginationPageOption(5)
            ->filters([
                ReservationFilters::status(),
                ReservationFilters::fromDate(),
                ReservationFilters::toDate(),
                ReservationFilters::machine(),
                ReservationFilters::operations(),
                ReservationFilters::assigned_user()
            ]);
        return ReservationTable::make($table, true);
    }
}
