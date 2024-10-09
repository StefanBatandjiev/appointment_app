<?php

namespace App\Filament\Resources\ClientResource\Widgets;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class StatsOverviewClientReservations extends BaseWidget
{
    public ?Client $record = null;
    protected function getStats(): array
    {
        $query = Reservation::query()->where('client_id', $this->record->id);
        $upcomingReservations = (clone $query)->where('start_time', '>', now())->where('status', '!=', ReservationStatus::CANCELED)->count();
        $totalReservations = $query->count();
        $totalRevenue = $query->where('status', '=', ReservationStatus::FINISHED)->get()->sum('total_price');

        return [
            Stat::make('Total Reservations', $totalReservations)
                ->label(new HtmlString('<span class="text-primary-600 text-base">Total Reservations</span>'))
                ->value(new HtmlString("<span class='text-primary-600 font-bold'>{$totalReservations}</span>")),
            Stat::make('Revenue', formatNumber($totalRevenue) . ' MKD')
                ->label(new HtmlString('<span class="text-primary-600 text-base">Revenue</span>'))
                ->value(new HtmlString("<span class='text-primary-600 font-bold'>{$totalRevenue} MKD</span>")),
            Stat::make('Upcoming Reservations', $upcomingReservations)
                ->label(new HtmlString('<span class="text-primary-600 text-base">Upcoming Reservations</span>'))
                ->value(new HtmlString("<span class='text-primary-600 font-bold'>{$upcomingReservations}</span>")),
        ];
    }
}
