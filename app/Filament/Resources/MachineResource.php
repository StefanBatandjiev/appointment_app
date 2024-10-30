<?php

namespace App\Filament\Resources;

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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MachineResource extends Resource
{
    protected static ?string $model = Machine::class;

    protected static ?string $navigationGroup = 'Setup Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewMachine::class,
            Pages\EditMachine::class,
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
                ->schema([
                    Section::make('Machine Details')
                        ->schema([
                            TextEntry::make('name')
                                ->label('Machine Name')
                                ->icon('heroicon-o-cog'),

                            TextEntry::make('operations.name')
                                ->label('Operations')
                                ->lineClamp(3)
                                ->icon('heroicon-o-queue-list'),

                            TextEntry::make('description')
                                ->label('Machine Description')
                                ->default('N/A')
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
                            ->required()
                            ->suffixIcon('heroicon-o-cog')
                            ->suffixIconColor('primary'),
                        Select::make('operations')
                            ->relationship('operations', 'name')
                            ->options(Operation::all()->pluck('name', 'id'))
                            ->label('Operations')
                            ->suffixIcon('heroicon-o-queue-list')
                            ->suffixIconColor('primary')
                            ->multiple(),
                        Textarea::make('description')
                            ->label('Machine Description')
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
                    ->state(function (Machine $machine) {
                        $cogSvg = svg('heroicon-o-cog', 'w-5 h-5 mx-1')->toHtml();

                        return new HtmlString("
                            <div class=\"flex flex-row\">
                                {$cogSvg}<span class=\"text-sm text-gray-700 mx-1 font-semibold\">{$machine->name}</span>
                             </div>
                        ");
                    }),
                TextColumn::make('description')
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
                    ->label('Operations')
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
            'index' => Pages\ListMachines::route('/'),
            'view' => Pages\ViewMachine::route('/{record}'),
            'create' => Pages\CreateMachine::route('/create'),
            'edit' => Pages\EditMachine::route('/{record}/edit'),
        ];
    }
}
