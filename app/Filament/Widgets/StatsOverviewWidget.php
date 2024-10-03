<?php

namespace App\Filament\Widgets;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $startDateThisMonth = Carbon::now()->startOfMonth();
        $endDateThisMonth = Carbon::now();

        $startDateLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endDateLastMonth = Carbon::now()->subMonth();

        $revenueThisMonth = Reservation::query()
            ->where('start_time', '>=', $startDateThisMonth)
            ->where('end_time', '<=', $endDateThisMonth)
            ->where('status', '!=', ReservationStatus::CANCELED)
            ->get()
            ->sum('total_price');

        $newClientsThisMonth = Client::query()
            ->where('created_at', '>=', $startDateThisMonth)
            ->get()
            ->count();

        $newReservationsThisMonth = Reservation::query()
            ->where('created_at', '>=', $startDateThisMonth)
            ->get()
            ->count();

        $revenueLastMonth = Reservation::query()
            ->where('start_time', '>=', $startDateLastMonth)
            ->where('end_time', '<=', $endDateLastMonth)
            ->where('status', '!=', ReservationStatus::CANCELED)
            ->get()
            ->sum('total_price');

        $newClientsLastMonth = Client::query()
            ->where('created_at', '>=', $startDateLastMonth)
            ->where('created_at', '<=', $endDateLastMonth)
            ->get()
            ->count();

        $newReservationsLastMonth = Reservation::query()
            ->where('created_at', '>=', $startDateLastMonth)
            ->where('created_at', '<=', $endDateLastMonth)
            ->get()
            ->count();

        $revenueChange = $revenueThisMonth - $revenueLastMonth;
        $clientsChange = $newClientsThisMonth - $newClientsLastMonth;
        $reservationsChange = $newReservationsThisMonth - $newReservationsLastMonth;

        $thisMonthChartData = $this->getChartData($startDateThisMonth);

        $formatNumber = function (int $number): string {
            if ($number < 1000) {
                return (string) number_format($number, 0);
            } elseif ($number < 1000000) {
                return number_format($number / 1000, 2) . 'k';
            } else {
                return number_format($number / 1000000, 2) . 'm';
            }
        };

        return [
            Stat::make('Revenue', '$' . $formatNumber($revenueThisMonth))
                ->description(($revenueChange >= 0 ? '+' : '') . $formatNumber($revenueChange) . ' from last month')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($thisMonthChartData['revenue'])
                ->color($revenueChange >= 0 ? 'primary' : 'danger'),

            Stat::make('New clients', $formatNumber($newClientsThisMonth))
                ->description(($clientsChange >= 0 ? '+' : '') . $formatNumber($clientsChange) . ' from last month')
                ->descriptionIcon($clientsChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($thisMonthChartData['new_clients'])
                ->color($clientsChange >= 0 ? 'primary' : 'danger'),

            Stat::make('New reservations', $formatNumber($newReservationsThisMonth))
                ->description(($reservationsChange >= 0 ? '+' : '') . $formatNumber($reservationsChange) . ' from last month')
                ->descriptionIcon($reservationsChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($thisMonthChartData['new_reservations'])
                ->color($reservationsChange >= 0 ? 'primary' : 'danger'),
        ];
    }

    private function getChartData($startDate)
    {
        $chartData = [
            'revenue' => [],
            'new_clients' => [],
            'new_reservations' => [],
        ];

        for ($day = 1; $day <= $startDate->diffInDays(now()); $day++) {
            $currentDate = Carbon::parse($startDate)->day($day);
            $nextDay = (clone $currentDate)->addDay();

            $dailyRevenue = Reservation::query()
                ->where('start_time', '>=', $currentDate)
                ->where('end_time', '<=', $nextDay)
                ->get()
                ->sum('total_price');
            $chartData['revenue'][] = $dailyRevenue;

            $newClients = Client::query()
                ->whereDate('created_at', $currentDate)
                ->get()
                ->count();
            $chartData['new_clients'][] = $newClients;

            $newReservations = Reservation::query()
                ->whereDate('created_at', $currentDate)
                ->get()
                ->count();
            $chartData['new_reservations'][] = $newReservations;
        }

        return $chartData;
    }
}
