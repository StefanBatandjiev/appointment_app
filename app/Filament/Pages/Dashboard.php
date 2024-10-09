<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ReservationsChart;
use App\Filament\Widgets\ReservationsTotalRevenueChart;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\UpcomingReservations;

class Dashboard extends \Filament\Pages\Dashboard
{

    protected static string $view = 'filament.pages.dashboard';
    protected function getFooterWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            ReservationsChart::class,
            ReservationsTotalRevenueChart::class,
            UpcomingReservations::class
        ];
    }
}
