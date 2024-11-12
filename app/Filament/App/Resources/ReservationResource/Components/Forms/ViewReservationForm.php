<?php

namespace App\Filament\App\Resources\ReservationResource\Components\Forms;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\Reservation;
use App\Models\User;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ViewReservationForm
{
    public static function form($refreshRecords = null): array
    {
        return [
                Placeholder::make('status')
                    ->label(function () {
                        $label = __('Status');
                        return new HtmlString("<span class='text-lg font-extralight text-center'>{$label}</span>");
                    })
                    ->content(function (Reservation $record): HtmlString {
                        $svg = match ($record->getReservationStatusAttribute()) {
                            ReservationStatus::ONGOING => svg('heroicon-o-minus-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(79%) sepia(66%) saturate(2254%) hue-rotate(352deg) brightness(103%) contrast(104%);'])->toHtml(),
                            ReservationStatus::SCHEDULED => svg('heroicon-o-clock', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(53%) sepia(20%) saturate(1649%) hue-rotate(81deg) brightness(94%) contrast(88%);'])->toHtml(),
                            ReservationStatus::FINISHED => svg('heroicon-o-check-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(31%) sepia(28%) saturate(6136%) hue-rotate(200deg) brightness(104%) contrast(105%);'])->toHtml(),
                            ReservationStatus::CANCELED => svg('heroicon-o-x-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(38%) sepia(68%) saturate(5599%) hue-rotate(337deg) brightness(90%) contrast(90%);'])->toHtml(),
                            ReservationStatus::PENDING_FINISH => svg('heroicon-o-exclamation-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(46%) sepia(17%) saturate(241%) hue-rotate(167deg) brightness(94%) contrast(86%);'])->toHtml(),
                        };

                        $value = __($record->getReservationStatusAttribute()->value);

                        return new HtmlString("
                            <div class=\"flex items-center\">
                                <span class=\"px-1 text-lg\">{$value}</span>
                                {$svg}
                            </div>
                        ");
                    }),
                Placeholder::make('total_price')
                    ->label(function () {
                        $label = __('Total Price');
                        return new HtmlString("<span class='text-lg font-extralight text-center'>{$label}</span>");
                    })
                    ->content(function (Reservation $record): HtmlString {
                        $label = $record->getTotalPriceAttribute() . __(' MKD');
                        return new HtmlString("
                            <span class=\"px-1 text-lg font-semibold text-center\">{$label}</span>
                        ");
                    }),
                Actions::make([
                    \Filament\Forms\Components\Actions\Action::make('Cancel Reservation')
                        ->label(__('Cancel Reservation'))
                        ->color('danger')
                        ->hidden(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::CANCELED ||
                            $record->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                            $record->getReservationStatusAttribute() === ReservationStatus::FINISHED)
                        ->action(function (Reservation $record) use ($refreshRecords) {
                            try {
                                $record->update(['status' => ReservationStatus::CANCELED]);

                                if (is_callable($refreshRecords)) {
                                    call_user_func($refreshRecords);
                                }

                                time_nanosleep(1, 0);

                                Notification::make()
                                    ->title(__('Canceled Reservation'))
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {

                                Notification::make()
                                    ->title(__('Failed to cancel reservation.'))
                                    ->danger()
                                    ->send();

                            }
                        }),
                    \Filament\Forms\Components\Actions\Action::make('Finish Reservation')
                        ->label(__('Finish Reservation'))
                        ->color('primary')
                        ->visible(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH)
                        ->hidden(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::FINISHED || $record->getReservationStatusAttribute() === ReservationStatus::CANCELED)
                        ->action(function ($record) use ($refreshRecords) {
                            try {
                                $record->update(['status' => ReservationStatus::FINISHED]);

                                if (is_callable($refreshRecords)) {
                                    call_user_func($refreshRecords);
                                }

                                time_nanosleep(1, 0);

                                Notification::make()
                                    ->title(__('Finished Reservation'))
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {

                                Notification::make()
                                    ->title(__('Failed to finish reservation.'))
                                    ->danger()
                                    ->send();
                            }
                        }),
                    \Filament\Forms\Components\Actions\Action::make('Invoice')
                        ->icon('heroicon-o-document-arrow-down')
                        ->label(__('Generate Invoice'))
                        ->color('primary')
                        ->visible(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::FINISHED)
                        ->url(fn (Reservation $record) => route('reservation.invoice.download', $record))->openUrlInNewTab(),
                ])->verticallyAlignCenter(),
                Select::make('client_id')
                    ->label(__('Client Name'))
                    ->options(Client::all()->pluck('name', 'id'))
                    ->suffixIcon('heroicon-o-user')
                    ->suffixIconColor('primary')
                    ->disabled(),
                Select::make('client_id')
                    ->label(__('Client Telephone'))
                    ->options(Client::all()->pluck('telephone', 'id')->map(fn($telephone) => $telephone ?? 'N/A'))
                    ->suffixIcon('heroicon-o-phone')
                    ->suffixIconColor('primary')
                    ->disabled(),
                Select::make('client_id')
                    ->label(__('Client Email'))
                    ->options(Client::all()->pluck('email', 'id')->map(fn($email) => $email ?? 'N/A'))
                    ->suffixIcon('heroicon-o-envelope')
                    ->suffixIconColor('primary')
                    ->disabled(),
                Select::make('user_id')
                    ->label(__('Created By User'))
                    ->options(User::all()->pluck('name', 'id')->lazy())
                    ->suffixIcon('heroicon-o-user-circle')
                    ->suffixIconColor('primary')
                    ->disabled()
                    ->columnSpan(2),
                Select::make('assigned_user_id')
                    ->label(__('Assigned User'))
                    ->options(User::all()->pluck('name', 'id')->lazy())
                    ->suffixIcon('heroicon-o-user')
                    ->suffixIconColor('primary')
                    ->disabled(),
                Select::make('machine_id')
                    ->label(__('Machine'))
                    ->options(Machine::all()->pluck('name', 'id')->lazy())
                    ->suffixIcon('heroicon-o-cog')
                    ->suffixIconColor('primary')
                    ->disabled()
                    ->columnSpan(2),
                Select::make('operations')
                    ->relationship('operations', 'name')
                    ->label(__('Operations'))
                    ->options(
                        Operation::all()
                            ->mapWithKeys(fn($operation) => [
                                $operation->id => "{$operation->name} - {$operation->price}" . __(' MKD')
                            ])
                            ->lazy()
                    )
                    ->multiple()
                    ->suffixIcon('heroicon-o-queue-list')
                    ->suffixIconColor('primary')
                    ->disabled(),
                DateTimePicker::make('start_time')
                    ->label(__('Date and Start Time'))
                    ->suffixIcon('heroicon-o-calendar-date-range')
                    ->suffixIconColor('primary')
                    ->disabled(),
                TimePicker::make('end_time')
                    ->label(__('End Time'))
                    ->suffixIcon('heroicon-o-calendar-date-range')
                    ->suffixIconColor('primary')
                    ->disabled(),
                TimePicker::make('break_time')
                    ->label(__('Break Time Till'))
                    ->suffixIcon('heroicon-o-calendar-date-range')
                    ->suffixIconColor('primary')
                    ->disabled()
            ];
    }
}
