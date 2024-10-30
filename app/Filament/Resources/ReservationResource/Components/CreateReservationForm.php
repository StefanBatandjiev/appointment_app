<?php

namespace App\Filament\Resources\ReservationResource\Components;

use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\User;
use App\Services\ReservationService;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

class CreateReservationForm
{

    public static function form(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    Select::make('client_id')
                        ->label('Client')
                        ->options(Client::all()->pluck('name', 'id'))
                        ->searchable()
                        ->suffixIcon('heroicon-o-user')
                        ->suffixIconColor('primary')
                        ->required()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Name')
                                ->required(),
                            TextInput::make('email')
                                ->label('Email')
                                ->required()
                                ->email()
                                ->unique(Client::class, 'email'),
                            TextInput::make('telephone')
                                ->label('Telephone')
                                ->unique(Client::class, 'telephone')
                                ->nullable(),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            $client = Client::query()->create([
                                'name' => $data['name'],
                                'email' => $data['email'],
                                'telephone' => $data['telephone'],
                            ]);

                            return $client->id;
                        })
                        ->columnSpan(3),
                    Select::make('machine_id')
                        ->label('Machine')
                        ->options(Machine::all()->pluck('name', 'id'))
                        ->suffixIcon('heroicon-o-cog')
                        ->suffixIconColor('primary')
                        ->required()
                        ->live()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            $set('operations', null);
                            $set('date', null);
                            $set('start_time', null);
                            $set('duration', null);
                            $set('break', null);
                        })
                        ->columnSpan(2),
                    Select::make('assigned_user_id')
                        ->label('Assigned User')
                        ->options(User::all()->pluck('name', 'id'))
                        ->suffixIcon('heroicon-o-user')
                        ->suffixIconColor('primary')
                        ->nullable()
                        ->searchable()
                        ->columnSpan(1),
                    Select::make('operations')
                        ->relationship('operations', 'name')
                        ->label('Operations')
                        ->multiple()
                        ->suffixIcon('heroicon-o-queue-list')
                        ->suffixIconColor('primary')
                        ->searchable()
                        ->required()
                        ->hidden(fn(Get $get) => !$get('machine_id'))
                        ->options(
                            fn(Get $get) => Operation::query()->whereHas('machines', function ($query) use ($get) {
                                $query->where('machine_id', $get('machine_id'));
                            })->pluck('name', 'id')
                        )
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
                        })->live()
                        ->columnSpan(3),
                    DatePicker::make('date')
                        ->minDate(now()->format('Y-m-d'))
                        ->maxDate(now()->addMonths(2)->format('Y-m-d'))
                        ->hidden(fn(Get $get) => !$get('operations'))
                        ->suffixIcon('heroicon-o-calendar-date-range')
                        ->suffixIconColor('primary')
                        ->required()
                        ->live()
                        ->columnSpan(3),
                    Select::make('start_time')
                        ->options(fn(Get $get) => ReservationService::getAvailableTimesForDate($get('machine_id'), $get('date')))
                        ->hidden(fn(Get $get) => !$get('date'))
                        ->suffixIcon('heroicon-o-clock')
                        ->suffixIconColor('primary')
                        ->required()
                        ->searchable()
                        ->live(),
                    Select::make('duration')
                        ->label('Duration')
                        ->options(fn(Get $get) => ReservationService::getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
                        ->helperText(fn(Get $get) => ReservationService::getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
                        ->hidden(fn(Get $get) => !$get('start_time'))
                        ->suffixIcon('heroicon-o-clock')
                        ->suffixIconColor('primary')
                        ->required()
                        ->live(),
                    Select::make('break')
                        ->label('Break Time')
                        ->options(fn(Get $get) => ReservationService::getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
                        ->helperText('You can add a break time after the reservation')
                        ->hidden(fn(Get $get) => !$get('duration'))
                        ->suffixIcon('heroicon-o-clock')
                        ->suffixIconColor('primary')
                        ->disabled(fn(Get $get) => ReservationService::disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
                ])->columnSpan(2),
        ];
    }
}
