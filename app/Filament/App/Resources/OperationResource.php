<?php

namespace App\Filament\App\Resources;

use App\Filament\Resources\OperationResource\Pages;
use App\Filament\Resources\OperationResource\RelationManagers;
use App\Models\Machine;
use App\Models\Operation;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OperationResource extends Resource
{
    protected static ?string $model = Operation::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationGroup(): string
    {
        return __('Setup Management');
    }

    public static function getModelLabel(): string
    {
        return __('Operation');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Operations');
    }

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
           \App\Filament\App\Resources\OperationResource\Pages\ViewOperation::class,
           \App\Filament\App\Resources\OperationResource\Pages\EditOperation::class
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('Operation Details'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Operation Name'))
                            ->icon('heroicon-o-bars-2'),

                        TextEntry::make('machines.name')
                            ->label(__('Machines'))
                            ->lineClamp(3)
                            ->icon('heroicon-o-cog'),

                        TextEntry::make('price')
                            ->label(__('Price'))
                            ->formatStateUsing(fn ($state) =>
                                number_format($state, 2, '.', ',') . __(' MKD')),

                        ColorEntry::make('color')
                            ->label(__('Color')),

                        TextEntry::make('description')
                            ->label(__('Operation Description'))
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
                            ->label(__('Operation Name'))
                            ->required()
                            ->suffixIcon('heroicon-o-bars-2')
                            ->suffixIconColor('primary'),
                        Select::make('Machines')
                            ->relationship('machines', 'name')
                            ->options(Machine::all()->pluck('name', 'id'))
                            ->placeholder(__('Select a machine'))
                            ->label(__('Machines'))
                            ->suffixIcon('heroicon-o-cog')
                            ->suffixIconColor('primary')
                            ->multiple(),
                        TextInput::make('price')
                            ->label(__('Price'))
                            ->numeric()
                            ->required()
                            ->suffixIcon('heroicon-o-banknotes')
                            ->suffixIconColor('primary'),
                        ColorPicker::make('color')
                            ->label(__('Color'))
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label(__('Operation Description'))
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
                    ->label(__('Operation Name'))
                    ->state(function (Operation $operation) {
                        $barsSvg = svg('heroicon-o-bars-2', 'w-5 h-5 mx-1')->toHtml();

                        return new HtmlString("
                            <div class=\"flex flex-row\">
                                {$barsSvg}<span class=\"text-sm text-gray-700 mx-1 font-semibold\">{$operation->name}</span>
                             </div>
                        ");
                    }),
                TextColumn::make('description')
                    ->label(__('Operation Description'))
                    ->words(10)
                    ->wrap()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ',') . __(' MKD'))
                    ->label(__('Price')),
                ColorColumn::make('color')
                    ->label(__('Color')),
                TextColumn::make('machines.name')
                    ->label(__('Machines'))
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
            'index' => \App\Filament\App\Resources\OperationResource\Pages\ListOperations::route('/'),
            'create' => \App\Filament\App\Resources\OperationResource\Pages\CreateOperation::route('/create'),
            'view' => \App\Filament\App\Resources\OperationResource\Pages\ViewOperation::route('/{record}'),
            'edit' => \App\Filament\App\Resources\OperationResource\Pages\EditOperation::route('/{record}/edit'),
        ];
    }
}
