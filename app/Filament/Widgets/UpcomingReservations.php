<?php

namespace App\Filament\Widgets;

use App\Enums\ReservationStatus;
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
        return $table
            ->query(Reservation::query()
                    ->where('start_time', '>=', now())
                    ->where('status', '!=', ReservationStatus::CANCELED)
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('start_time')
            ->columns([
                TextColumn::make('start_time')->label('Date and Start Time')->dateTime('D, d M Y H:i')->color(Color::Blue),
                TextColumn::make('end_time')->label('End Time')->time('H:i'),
                TextColumn::make('break_time')->label('Break Till')->time('H:i'),
                TextColumn::make('client.name')->label('Client'),
                TextColumn::make('machine.name')->label('Machine'),
                TextColumn::make('assigned_user.name')->label('Assigned User'),
                TextColumn::make('operations.name')->label('Operations'),
                TextColumn::make('total_price')
                    ->formatStateUsing(fn($state) => number_format($state, 2, '.', ',') . ' MKD')
                    ->label('Total Price'),
                TextColumn::make('status')
                    ->icon('heroicon-o-clock')
                    ->color('success')
                    ->label('Status')
            ])
            ->actions([
                Tables\Actions\Action::make('Edit')
                    ->url(fn (Reservation $record): string => ReservationResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
