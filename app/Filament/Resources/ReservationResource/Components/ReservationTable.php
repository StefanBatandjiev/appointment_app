<?php

namespace App\Filament\Components;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Services\ReservationService;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Tables;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ReservationTable
{
    public static function make(Table $table, $client = false): Table
    {
        return $table
            ->recordAction('view')
            ->columns([
                TextColumn::make('start_end_break')
                    ->label('Start/End/Break')
                    ->state(function (Reservation $reservation): HtmlString {
                        $start_time = Carbon::parse($reservation->start_time);
                        $end_time = Carbon::parse($reservation->end_time);
                        $break_time = $reservation->break_time ? Carbon::parse($reservation->break_time) : null;

                        return new HtmlString("
                            <div class=\"flex flex-col\">
                                <span class=\"text-gray-700 text-sm font-semibold\">{$start_time->format('D, d M Y')}</span>
                                <span class=\"text-primary-600 text-xs\">Start Time: <span class=\"font-semibold text-sm\">{$start_time->format('H:i')}</span></span>
                                <span class=\"text-green-600 text-xs\">End Time: <span class=\"font-semibold text-sm\">{$end_time->format('H:i')}</span></span>
                                " . ($break_time ? "<span class=\"text-danger-600 text-xs\">Break Time Till: <span class=\"font-semibold text-sm\">{$break_time->format('H:i')}</span></span>" : "") . "
                            </div>
                        ");
                    })->verticallyAlignStart(),
                TextColumn::make('client')->label('Client')
                    ->state(function (Reservation $reservation) {
                        $emailSVG = svg('heroicon-o-envelope', 'w-5 h-5 mx-1')->toHtml();
                        $phoneSVG = svg('heroicon-o-phone', 'w-5 h-5 mx-1')->toHtml();

                        return new HtmlString("
                            <div class=\"flex flex-col\">
                                <span class=\"text-sm text-gray-700 mx-1 font-semibold\">{$reservation->client->name}</span>
                                <span class=\"flex text-sm text-gray-500\">{$emailSVG} {$reservation->client->email}</span>
                                <span class=\"flex text-sm text-gray-500\">{$phoneSVG} {$reservation->client->telephone}</span>
                             </div>
                        ");
                    })->verticallyAlignStart()->hidden($client),
                TextColumn::make('machine')
                    ->label('Machine')
                    ->state(function (Reservation $reservation) {
                        $operationsList = $reservation->operations->map(function ($operation) {
                            return "
                                <div class=\"flex items-center space-x-2\">
                                    <div class=\"w-4 h-4 inline-block\" style=\"background-color: {$operation->color};\"></div>
                                    <span class=\"text-sm text-gray-500\">{$operation->name} - " . number_format($operation->price, 2, '.', ',') . " MKD</span>
                                </div>
                            ";
                        })->join('');

                        return new HtmlString("
                            <div class=\"flex flex-col\">
                                <span class=\"text-sm text-gray-700 font-semibold\">{$reservation->machine->name}</span>
                                <span class=\"flex flex-col text-sm text-gray-500\">
                                    {$operationsList}
                                </span>
                             </div>
                        ");
                    })->verticallyAlignStart(),
                TextColumn::make('assigned_user.name')
                    ->label('Assigned User')
                    ->state(function (Reservation $reservation) {
                        return new HtmlString("
                            <div class=\"flex flex-col items-center\">
                                <span class=\"text-base text-gray-700 font-semibold\">{$reservation->assigned_user->name}</span>
                            </div>
                        ");
                    })->alignCenter(),
                TextColumn::make('user.name')
                    ->label('Created By User')
                    ->state(function (Reservation $reservation) {
                        return new HtmlString("
                            <div class=\"flex flex-col items-center\">
                                <span class=\"text-base text-gray-700 font-semibold\">{$reservation->user->name}</span>
                            </div>
                        ");
                    })->alignCenter(),
                TextColumn::make('combined_status_price')
                    ->label('Status & Total Price')
                    ->state(function (Reservation $record) {
                        $svg = match ($record->getReservationStatusAttribute()) {
                            ReservationStatus::ONGOING => svg('heroicon-o-minus-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(79%) sepia(66%) saturate(2254%) hue-rotate(352deg) brightness(103%) contrast(104%);'])->toHtml(),
                            ReservationStatus::SCHEDULED => svg('heroicon-o-clock', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(53%) sepia(20%) saturate(1649%) hue-rotate(81deg) brightness(94%) contrast(88%);'])->toHtml(),
                            ReservationStatus::FINISHED => svg('heroicon-o-check-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(31%) sepia(28%) saturate(6136%) hue-rotate(200deg) brightness(104%) contrast(105%);'])->toHtml(),
                            ReservationStatus::CANCELED => svg('heroicon-o-x-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(38%) sepia(68%) saturate(5599%) hue-rotate(337deg) brightness(90%) contrast(90%);'])->toHtml(),
                            ReservationStatus::PENDING_FINISH => svg('heroicon-o-exclamation-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(46%) sepia(17%) saturate(241%) hue-rotate(167deg) brightness(94%) contrast(86%);'])->toHtml(),
                        };

                        $statusColor = match ($record->getReservationStatusAttribute()) {
                            ReservationStatus::ONGOING => 'text-warning-500',
                            ReservationStatus::SCHEDULED => 'text-success-500',
                            ReservationStatus::FINISHED => 'text-primary-500',
                            ReservationStatus::CANCELED => 'text-danger-500',
                            ReservationStatus::PENDING_FINISH => 'text-gray-500',
                        };

                        $formattedPrice = number_format($record->total_price, 2, '.', ',') . ' MKD';

                        return new HtmlString("
                            <div class='flex flex-col'>
                                <div class='flex items-center my-2'>
                                    {$svg}
                                    <span class=\"{$statusColor}\">{$record->getReservationStatusAttribute()->value}</span>
                                </div>
                                <span class='text-center text-base font-semibold text-gray-700'>{$formattedPrice}</span>
                            </div>
                        ");
                    })
            ])
            ->defaultSort(function (Builder $query) {
                $currentDate = now();

                return $query
                    ->orderByRaw("CASE
                        WHEN status = ? THEN 5
                        WHEN end_time <= ? AND status = ? THEN 3
                        WHEN start_time <= ? AND end_time >= ? THEN 1
                        WHEN start_time >= ? THEN 2
                        WHEN status = ? THEN 4
                    END", [
                        ReservationStatus::CANCELED->value,
                        $currentDate,
                        ReservationStatus::SCHEDULED->value,
                        $currentDate,
                        $currentDate,
                        $currentDate,
                        ReservationStatus::FINISHED->value,
                    ])
                    ->orderBy('start_time');
            })
            ->actions([
                Tables\Actions\ActionGroup::make([
                    ViewAction::make('view')
                        ->form([
                            Section::make()->schema(
                                ViewReservationForm::form()
                            )->columns(3)->columnSpan(2)
                        ])->slideOver(),
                    EditAction::make('edit')
                        ->form(ReservationService::editForm()),
                    Tables\Actions\DeleteAction::make(),
                ])
            ]);
    }
}
