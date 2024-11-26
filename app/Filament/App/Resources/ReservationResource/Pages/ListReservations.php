<?php

namespace App\Filament\App\Resources\ReservationResource\Pages;

use App\Enums\ReservationStatus;
use App\Filament\App\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getSubNavigation(): array
    {
        return [

        ];
    }

    public function getTabs(): array
    {
        $currentDate = now();

        $baseQuery = $this->getModel()::query()
            ->where('status', '!=', ReservationStatus::CANCELED);

        $ongoingQuery = (clone $baseQuery)
            ->where('start_time', '<=', $currentDate)
            ->where('end_time', '>=', $currentDate);

        $scheduledQuery = (clone $baseQuery)
            ->where('start_time', '>=', $currentDate);

        $pendingFinishQuery = (clone $baseQuery)
            ->where('end_time', '<=', $currentDate)
            ->where('status', '!=', ReservationStatus::FINISHED);

        $finishedQuery = $this->getModel()::query()
            ->where('status', '=', ReservationStatus::FINISHED);

        $canceledQuery = $this->getModel()::query()
            ->where('status', '=', ReservationStatus::CANCELED);

        return [
            'all' => Tab::make(__('All'))
                ->badge($this->getModel()::count()),

            'ongoing' => Tab::make(__('Ongoing'))
                ->modifyQueryUsing(function ($query) use ($ongoingQuery) {
                    $query->mergeConstraintsFrom($ongoingQuery);
                })
                ->badge($ongoingQuery->count())
                ->badgeColor('warning')
                ->extraAttributes(['style' => "background-color: rgb(254, 215, 170)", 'class' => "whitespace-nowrap px-2 py-1 text-sm md:text-base md:px-4"]),

            'scheduled' => Tab::make(__('Scheduled'))
                ->modifyQueryUsing(function ($query) use ($scheduledQuery) {
                    $query->mergeConstraintsFrom($scheduledQuery);
                })
                ->badge($scheduledQuery->count())
                ->badgeColor('success')
                ->extraAttributes(['style' => "background-color: rgb(187, 247, 208)", 'class' => "whitespace-nowrap px-2 py-1 text-sm md:text-base md:px-4"]),

            'pendingFinish' => Tab::make(__('Pending Finish'))
                ->modifyQueryUsing(function ($query) use ($pendingFinishQuery) {
                    $query->mergeConstraintsFrom($pendingFinishQuery);
                })
                ->badge($pendingFinishQuery->count())
                ->badgeColor('gray')
                ->extraAttributes(['style' => "background-color: rgb(229, 231, 235)", 'class' => "whitespace-nowrap px-2 py-1 text-sm md:text-base md:px-4"]),

            'finished' => Tab::make(__('Finished'))
                ->modifyQueryUsing(function ($query) use ($finishedQuery) {
                    $query->mergeConstraintsFrom($finishedQuery);
                })
                ->badge($finishedQuery->count())
                ->badgeColor('primary')
                ->extraAttributes(['style' => "background-color: rgb(191, 219, 254)", 'class' => "whitespace-nowrap px-2 py-1 text-sm md:text-base md:px-4"]),

            'canceled' => Tab::make(__('Canceled'))
                ->modifyQueryUsing(function ($query) use ($canceledQuery) {
                    $query->mergeConstraintsFrom($canceledQuery);
                })
                ->badge($canceledQuery->count())
                ->badgeColor('danger')
                ->extraAttributes(['style' => "background-color: rgb(254, 202, 202)", 'class' => "whitespace-nowrap px-2 py-1 text-sm md:text-base md:px-4"]),
        ];
    }

}
