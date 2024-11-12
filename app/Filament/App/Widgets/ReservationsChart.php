<?php

namespace App\Filament\App\Widgets;

use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class ReservationsChart extends ChartWidget
{
    public function getHeading(): string|Htmlable|null
    {
        return __('Reservations Per Month');
    }

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
                    'label' => __('Reservations'),
                    'data' => $monthlyReservations,
                    'fill' => 'start',
                ],
            ],
            'labels' => [
                __('Jan'),
                __('Feb'),
                __('Mar'),
                __('Apr'),
                __('May'),
                __('Jun'),
                __('Jul'),
                __('Aug'),
                __('Sep'),
                __('Oct'),
                __('Nov'),
                __('Dec')
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
