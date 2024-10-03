<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ReservationsChart;
use App\Filament\Widgets\ReservationsTotalRevenueChart;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\UpcomingReservations;

class Dashboard extends \Filament\Pages\Dashboard
{

    protected static string $view = 'filament.pages.dashboard';

//    public function filtersForm(Form $form): Form
//    {
//        return $form
//            ->schema([
//                Section::make()
//                    ->schema([
//                        DatePicker::make('startDate')
//                            ->maxDate(fn(Get $get) => $get('endDate') ?: now())->live(),
//                        DatePicker::make('endDate')
//                            ->minDate(fn(Get $get) => $get('startDate') ?: now())
//                            ->maxDate(now())->live(),
//                    ])
//                    ->columns(),
//            ]);
//    }

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
