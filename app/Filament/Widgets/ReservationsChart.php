<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ReservationsChart extends ChartWidget
{
    protected static ?string $heading = 'Reservations Per Month';

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;

        $monthlyReservations = array_fill(0, 12, 0);

        $reservations = Reservation::query()->selectRaw('strftime("%m", created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->get();

        foreach ($reservations as $reservation) {
            $monthlyReservations[$reservation->month - 1] = $reservation->count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Reservations',
                    'data' => $monthlyReservations,
                    'fill' => 'start',
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
