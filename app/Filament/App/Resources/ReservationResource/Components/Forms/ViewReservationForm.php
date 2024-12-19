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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
                })->columnSpan(['sm' => 2, 'md' => 1]),
            Placeholder::make('discount_price')
                ->label(function () {
                    $label = __('Discount & Total Price');
                    return new HtmlString("<span class='text-lg font-extralight text-center'>{$label}</span>");
                })
                ->content(function (Reservation $record): HtmlString {
                    $label = $record->getTotalPriceAttribute() . __(' MKD');
                    return new HtmlString("
                            <span class=\"px-1 text-lg text-danger-600 text-center\">{$record->discount}%</span>
                            <span class=\"px-1 text-lg text-gray-500 text-center\">{$label}</span>
                        ");
                })->columnSpan(['sm' => 2, 'md' => 1]),
            Placeholder::make('total_price')
                ->label(function () {
                    $label = __('Final Price');
                    return new HtmlString("<span class='text-lg font-extralight text-center'>{$label}</span>");
                })
                ->content(function (Reservation $record): HtmlString {
                    $label = $record->getDiscountedPriceAttribute() . __(' MKD');
                    return new HtmlString("
                            <span class=\"px-1 text-lg font-semibold text-center\">{$label}</span>
                        ");
                })->columnSpan(['sm' => 2, 'md' => 1]),
            Actions::make([
                \Filament\Forms\Components\Actions\Action::make('Cancel Reservation')
                    ->label(__('Cancel Reservation'))
                    ->color('danger')
                    ->hidden(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::CANCELED ||
                        $record->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                        $record->getReservationStatusAttribute() === ReservationStatus::FINISHED)
                    ->action(function (Reservation $record) use ($refreshRecords) {
                        $record->update(['status' => ReservationStatus::CANCELED]);

                        if (is_callable($refreshRecords)) {
                            call_user_func($refreshRecords);
                        }

                        time_nanosleep(1, 0);

                        Notification::make()
                            ->title(__('Canceled Reservation'))
                            ->success()
                            ->send();
                    }),
                \Filament\Forms\Components\Actions\Action::make('Finish Reservation')
                    ->label(__('Finish Reservation'))
                    ->color('primary')
                    ->visible(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH)
                    ->hidden(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::FINISHED || $record->getReservationStatusAttribute() === ReservationStatus::CANCELED)
                    ->modal()
                    ->form([
                        TextInput::make('discount')
                            ->label(__('Discount'))
                            ->numeric()
                            ->step('0.01')
                            ->suffixIcon('heroicon-o-percent-badge')
                            ->suffixIconColor('primary')
                            ->minValue(0)
                            ->maxValue(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $originalPrice = $get('original_price');
                                if ($originalPrice !== null) {
                                    $set('price', ceil($originalPrice * (1 - $state / 100)));
                                }
                            }),

                        TextInput::make('price')
                            ->label(__('Final Price'))
                            ->numeric()
                            ->default(fn(Reservation $record) => $record->getTotalPriceAttribute())
                            ->suffixIcon('heroicon-o-banknotes')
                            ->suffixIconColor('primary')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $originalPrice = $get('original_price');
                                if ($originalPrice !== null && $originalPrice > 0) {
                                    $set('discount', round((1 - $state / $originalPrice) * 100, 2));
                                }
                            }),

                        Hidden::make('original_price')
                            ->default(fn(Reservation $record) => $record->getTotalPriceAttribute()),

                    ])
                    ->action(function ($record, array $data) use ($refreshRecords) {
                        $record->update([
                            'status' => ReservationStatus::FINISHED,
                            'discount' => $data['discount'],
                        ]);

                        if (is_callable($refreshRecords)) {
                            call_user_func($refreshRecords);
                        }

                        time_nanosleep(1, 0);

                        Notification::make()
                            ->title(__('Finished Reservation'))
                            ->success()
                            ->send();
                    }),
                \Filament\Forms\Components\Actions\Action::make('Invoice')
                    ->icon('heroicon-o-document-arrow-down')
                    ->label(__('Generate Invoice'))
                    ->color('primary')
                    ->visible(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::FINISHED)
                    ->url(fn(Reservation $record) => route('reservation.invoice.download', $record))->openUrlInNewTab(),
            ])->verticallyAlignCenter()->columnSpan(['sm' => 2, 'md' => 1]),
            Select::make('client_id')
                ->label(__('Client Name'))
                ->options(Client::all()->pluck('name', 'id'))
                ->suffixIcon('heroicon-o-user')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2, 'md' => 1]),
            Select::make('client_id')
                ->label(__('Client Telephone'))
                ->options(Client::all()->pluck('telephone', 'id')->map(fn($telephone) => $telephone ?? 'N/A'))
                ->suffixIcon('heroicon-o-phone')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2, 'md' => 1]),
            Select::make('client_id')
                ->label(__('Client Email'))
                ->options(Client::all()->pluck('email', 'id')->map(fn($email) => $email ?? 'N/A'))
                ->suffixIcon('heroicon-o-envelope')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2, 'md' => 2]),
            Select::make('user_id')
                ->label(__('Created By User'))
                ->options(User::all()->pluck('name', 'id')->lazy())
                ->suffixIcon('heroicon-o-user-circle')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2]),
            Select::make('assigned_user_id')
                ->label(__('Assigned User'))
                ->options(User::all()->pluck('name', 'id')->lazy())
                ->suffixIcon('heroicon-o-user')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2, 'md' => 2]),
            Select::make('machine_id')
                ->label(__('Machine'))
                ->options(Machine::all()->pluck('name', 'id')->lazy())
                ->suffixIcon('heroicon-o-cog')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2]),
            Select::make('operations')
                ->relationship('operations', 'name')
                ->label(__('Operations'))
                ->options(function (Reservation $record) {
                    return $record->operations
                        ->mapWithKeys(function (Operation $operation) use ($record) {
                            $price = $operation->pivot->price ?? $operation->price;

                            if ($record->getReservationStatusAttribute() === ReservationStatus::FINISHED) {
                                $price = $operation->pivot->price ?? $price;
                            }

                            return [
                                $operation->id => "{$operation->name} - {$price} " . __('MKD')
                            ];
                        });
                })
                ->multiple()
                ->suffixIcon('heroicon-o-queue-list')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2, 'md' => 2]),
            DateTimePicker::make('start_time')
                ->label(__('Date and Start Time'))
                ->suffixIcon('heroicon-o-calendar-date-range')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2, 'md' => 2]),
            TimePicker::make('end_time')
                ->label(__('End Time'))
                ->suffixIcon('heroicon-o-calendar-date-range')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2, 'md' => 1]),
            TimePicker::make('break_time')
                ->label(__('Break Time Till'))
                ->suffixIcon('heroicon-o-calendar-date-range')
                ->suffixIconColor('primary')
                ->disabled()
                ->columnSpan(['sm' => 2, 'md' => 1])
        ];
    }
}
