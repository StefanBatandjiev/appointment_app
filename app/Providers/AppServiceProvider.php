<?php

namespace App\Providers;

use App\Models\Reservation;
use App\Models\Tenant;
use App\Observers\ReservationObserver;
use App\Observers\TenantObserver;
use App\Policies\ReservationPolicy;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        DateTimePicker::configureUsing(fn (DateTimePicker $component) => $component->format('D, d M Y H:i')->displayFormat('D, d M Y H:i')->native(false));
        DatePicker::configureUsing(fn (DatePicker $component) => $component->format('D, d M Y H:i')->displayFormat('D, d M Y')->native(false));

        TimePicker::configureUsing(fn (TimePicker $component) => $component->format("H:i")->displayFormat('H:i')->native(false));

        Gate::policy(Reservation::class, ReservationPolicy::class);

        Reservation::observe(ReservationObserver::class);

        Tenant::observe(TenantObserver::class);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['mk','en']);
        });

        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->simple()
                ->visible(fn (): bool => auth()->user()->is_admin());
        });
    }
}
