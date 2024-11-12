<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\ReservationsChart;
use App\Filament\App\Widgets\ReservationsTotalRevenueChart;
use App\Filament\App\Widgets\StatsOverviewWidget;
use App\Filament\App\Widgets\UpcomingReservations;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends \Filament\Pages\Dashboard
{
    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public function getTitle(): string|Htmlable
    {
        return '';
    }

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
