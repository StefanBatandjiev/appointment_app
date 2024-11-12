<?php

namespace App\Filament\App\Widgets;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class ReservationsTotalRevenueChart extends ChartWidget
{
    public function getHeading(): string|Htmlable|null
    {
        return __('Total Revenue');
    }

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;

        $monthlyRevenue = array_fill(0, 12, 0);

        $reservations = Reservation::query()
            ->whereYear('created_at', $currentYear)
            ->where('status', '=', ReservationStatus::FINISHED)
            ->get();

        foreach ($reservations as $reservation) {
            $monthIndex = (int)$reservation->created_at->format('m') - 1;
            $monthlyRevenue[$monthIndex] += $reservation->total_price;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Total Revenue'),
                    'data' => $monthlyRevenue,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
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
