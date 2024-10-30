<?php

namespace App\Filament\Resources\ReservationResource\Components;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;

class EditReservationForm
{
    public static function form($refreshRecords = null, $closeModal = null): array
    {
        return [
            Section::make('Reservation Editing Options')
                ->description(fn(Reservation $reservation) => match ($reservation->getReservationStatusAttribute()) {
                    ReservationStatus::ONGOING => 'You can change the assigned user, operations and duration of the reservation, and you can add break time after the reservation.',
                    ReservationStatus::PENDING_FINISH => 'You can change the assigned user, operations and duration of the reservation.',
                    default => 'You can change the assigned user, operations, and reschedule the reservation.',
                })
                ->schema([
                Select::make('assigned_user_id')
                    ->label('Assigned User')
                    ->options(User::all()->pluck('name', 'id')->lazy())
                    ->suffixIcon('heroicon-o-user')
                    ->suffixIconColor('primary'),
                Select::make('operations')
                    ->relationship('operations', 'name')
                    ->label('Operation')
                    ->options(
                        Operation::all()
                            ->mapWithKeys(fn($operation) => [
                                $operation->id => "{$operation->name} - {$operation->price} MKD"
                            ])
                            ->lazy()
                    )
                    ->multiple()
                    ->required()
                    ->suffixIcon('heroicon-o-queue-list')
                    ->suffixIconColor('primary')
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('description'),
                        TextInput::make('price')->numeric()->required(),
                        ColorPicker::make('color')->required()
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $operation = Operation::query()->create([
                            'name' => $data['name'],
                            'description' => $data['description'],
                            'color' => $data['color'],
                            'price' => $data['price'],
                        ]);

                        return $operation->id;
                    })->columnSpan(2),
                DatePicker::make('date')
                    ->minDate(function (Reservation $reservation) {
                        if ($reservation->getReservationStatusAttribute() === ReservationStatus::SCHEDULED) {
                            return now()->format('Y-m-d');
                        }
                        return null;
                    })
                    ->maxDate(now()->addMonths(2)->format('Y-m-d'))
                    ->columnSpan(3)
                    ->suffixIcon('heroicon-o-calendar-date-range')
                    ->suffixIconColor('primary')
                    ->disabled(fn(Reservation $reservation) =>
                        $reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                        $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING)
                    ->dehydrated()
                    ->live(),
                Select::make('start')
                    ->options(fn(Get $get) => ReservationService::getAvailableTimesForDate($get('machine_id'), $get('date'), $get('id')))
                    ->hidden(fn(Get $get) => !$get('date'))
                    ->disabled(fn(Reservation $reservation) =>
                        $reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                        $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING)
                    ->dehydrated()
                    ->required()
                    ->searchable()
                    ->suffixIcon('heroicon-o-clock')
                    ->suffixIconColor('primary')
                    ->live(),
                Select::make('duration')
                    ->label('Duration')
                    ->options(function (Reservation $reservation, Get $get) {
                        if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                            $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING) {

                            return ReservationService::getDurations($reservation->machine_id, $reservation->start_time, Carbon::parse($reservation->start_time)->format('H:i'));
                        }

                        return ReservationService::getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '');
                    })
                    ->helperText(function (Reservation $reservation, Get $get) {
                        if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                            $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING) {

                            return ReservationService::getNextReservationStartTime($reservation->machine_id, $reservation->start_time, Carbon::parse($reservation->start_time)->format('H:i'));
                        }

                        return ReservationService::getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '');
                    })
                    ->hidden(function (Reservation $reservation, Get $get) {
                        if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                            $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING) {

                            return false;
                        }

                        return !$get('start');
                    })
                    ->afterStateHydrated(function (Reservation $reservation, Set $set) {
                        if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                            $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING) {
                            $set('date', Carbon::parse($reservation->start_time)->format('Y-m-d'));
                            $set('start', Carbon::parse($reservation->start_time)->format('H:i'));
                        }
                    })
                    ->required()
                    ->suffixIcon('heroicon-o-clock')
                    ->suffixIconColor('primary')
                    ->live(),
                Select::make('break')
                    ->label('Break Time')
                    ->options(fn(Get $get) => ReservationService::getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))
                    ->helperText('You can add a break time after the reservation')
                    ->hidden(function (Reservation $reservation, Get $get) {
                        if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH) {

                            return true;
                        }

                        return !$get('duration');
                    })
                    ->suffixIcon('heroicon-o-clock')
                    ->suffixIconColor('primary')
                    ->disabled(fn(Get $get) => ReservationService::disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))
            ])->columns(3)->columnSpan(2)
        ];
    }
}
