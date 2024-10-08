<?php

namespace App\Filament\Resources\ClientResource\Widgets;

use App\Enums\ReservationStatus;
use App\Filament\Components\ReservationTable;
use App\Models\Client;
use App\Models\Reservation;
use App\Services\ReservationService;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ClientReservations extends BaseWidget
{

    public ?Client $record = null;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $table
            ->query(Reservation::query()->where('client_id', $this->record->id))->defaultPaginationPageOption(5)
            ->filters([
                SelectFilter::make('reservation_status')
                    ->options([
                        ReservationStatus::ONGOING->value => 'Ongoing',
                        ReservationStatus::SCHEDULED->value => 'Scheduled',
                        ReservationStatus::PENDING_FINISH->value => 'Pending Finish',
                        ReservationStatus::FINISHED->value => 'Finished',
                        ReservationStatus::CANCELED->value => 'Canceled',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $status = $data['value'];

                        $currentDate = now()->timezone('GMT+2');

                        if ($status === ReservationStatus::ONGOING->value) {
                            $query->where('start_time', '<=', $currentDate)->where('end_time', '>=', $currentDate)
                                ->where('status', '!=', ReservationStatus::CANCELED);
                        } elseif ($status === ReservationStatus::SCHEDULED->value) {
                            $query->where('start_time', '>=', $currentDate)
                                ->where('status', '!=', ReservationStatus::CANCELED);
                        } elseif ($status === ReservationStatus::PENDING_FINISH->value) {
                            $query->where('end_time', '<=', $currentDate)
                                ->where('status', '!=', ReservationStatus::CANCELED)
                                ->where('status', '!=', ReservationStatus::FINISHED);
                        } elseif ($status === ReservationStatus::FINISHED->value) {
                            $query->where('status', ReservationStatus::FINISHED);
                        } elseif ($status === ReservationStatus::CANCELED->value) {
                            $query->where('status', ReservationStatus::CANCELED);
                        }
                    })
                    ->label('Reservation Status'),

            ]);
        return ReservationTable::make($table, true);
    }
}
