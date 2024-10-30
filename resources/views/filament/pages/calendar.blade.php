<x-filament-panels::page>
    <label class="fi-fo-field-wrp-label inline-flex items-center" for="machineSelect">
        <span class="text-lg font-semibold text-gray-800 dark:text-white">
            Select a Machine for the Calendar:
        </span>
    </label>
    <x-filament::input.wrapper>
        <x-filament::input.select id="machineSelect" wire:model.live="selectedMachine">
            @foreach($machines as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </x-filament::input.select>

    </x-filament::input.wrapper>

    @livewire(\App\Filament\Widgets\CalendarWidget::class,
    ['selectedMachine' => $selectedMachine],
    key(str()->random()))

    @push('styles')
        <style>
            .gap-y-8 {
                gap: 0.8rem;
            }
        </style>
    @endpush
</x-filament-panels::page>
