<?php

namespace App\Filament\App\Resources;

use App\Filament\Resources\MachineResource\Pages;
use App\Filament\Resources\MachineResource\RelationManagers;
use App\Models\Machine;
use App\Models\Operation;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class MachineResource extends Resource
{
    protected static ?string $model = Machine::class;

    public static function getNavigationGroup(): string
    {
        return __('Setup Management');
    }

    public static function getModelLabel(): string
    {
        return __('Machine');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Machines');
    }

    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            \App\Filament\App\Resources\MachineResource\Pages\ViewMachine::class,
            \App\Filament\App\Resources\MachineResource\Pages\EditMachine::class,
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
                ->schema([
                    Section::make(__('Machine Details'))
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('Machine Name'))
                                ->icon('heroicon-o-cog')
                                ->columnSpan(['default' => 2, 'md' => 1]),

                            TextEntry::make('operations.name')
                                ->label(__('Operations'))
                                ->lineClamp(3)
                                ->icon('heroicon-o-queue-list')
                                ->columnSpan(['default' => 2, 'md' => 1]),

                            TextEntry::make('description')
                                ->label(__('Machine Description'))
                                ->default(__('N/A'))
                                ->columnSpan(2),
                        ])
                        ->columns()
                        ->columnSpan(2),
                ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Machine Name'))
                            ->required()
                            ->suffixIcon('heroicon-o-cog')
                            ->suffixIconColor('primary')
                            ->columnSpan(['default' => 2, 'md' => 1]),
                        Select::make('operations')
                            ->placeholder(__('Select an operation'))
                            ->label(__('Operations'))
                            ->relationship('operations', 'name')
                            ->options(Operation::all()->pluck('name', 'id'))
                            ->suffixIcon('heroicon-o-queue-list')
                            ->suffixIconColor('primary')
                            ->multiple()
                            ->columnSpan(['default' => 2, 'md' => 1]),
                        Textarea::make('description')
                            ->label(__('Machine Description'))
                            ->autosize()
                            ->columnSpan(2),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Machine Name'))
                    ->state(function (Machine $machine) {
                        $cogSvg = svg('heroicon-o-cog', 'w-5 h-5 mx-1')->toHtml();

                        return new HtmlString("
                            <div class=\"flex flex-row\">
                                {$cogSvg}<span class=\"text-sm text-gray-700 mx-1 font-semibold\">{$machine->name}</span>
                             </div>
                        ");
                    }),
                TextColumn::make('description')
                    ->label(__('Machine Description'))
                    ->words(10)
                    ->wrap()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('operations.name')
                    ->label(__('Operations'))
                    ->listWithLineBreaks()
                    ->limitList()
                    ->expandableLimitedList()
                    ->disabledClick(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\App\Resources\MachineResource\Pages\ListMachines::route('/'),
            'create' => \App\Filament\App\Resources\MachineResource\Pages\CreateMachine::route('/create'),
            'view' => \App\Filament\App\Resources\MachineResource\Pages\ViewMachine::route('/{record}'),
            'edit' => \App\Filament\App\Resources\MachineResource\Pages\EditMachine::route('/{record}/edit'),
        ];
    }
}
