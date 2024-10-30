<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CalendarWidget;
use App\Models\Machine;
use Filament\Pages\Page;

class Calendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.calendar';
    public static function getNavigationLabel(): string
    {
        return __('Calendar');
    }

    public array $machines;

    public ?string $selectedMachine = null;

    public function mount(): void
    {
        $this->machines = Machine::query()->pluck('name', 'id')->toArray();
        $this->selectedMachine = array_key_first($this->machines);
    }
}
