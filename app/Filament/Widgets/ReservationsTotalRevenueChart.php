<?php

namespace App\Filament\Widgets;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ReservationsTotalRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Total Revenue';

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;

        $monthlyRevenue = array_fill(0, 12, 0);

        $reservations = Reservation::query()
            ->whereYear('created_at', $currentYear)
            ->where('status', '!=', ReservationStatus::CANCELED)
            ->get();

        foreach ($reservations as $reservation) {
            $monthIndex = (int)$reservation->created_at->format('m') - 1;
            $monthlyRevenue[$monthIndex] += $reservation->total_price;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Revenue',
                    'data' => $monthlyRevenue,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
