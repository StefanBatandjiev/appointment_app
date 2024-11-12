<?php

namespace App\Filament\App\Pages;

use App\Models\Machine;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Calendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.calendar';
    public static function getNavigationLabel(): string
    {
        return __('Calendar');
    }

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public array $machines;

    public ?string $selectedMachine = null;

    public function mount(): void
    {
        $this->machines = Machine::query()->pluck('name', 'id')->toArray();
        $this->selectedMachine = array_key_first($this->machines);
    }
}
