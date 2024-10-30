<?php

namespace App\Filament\Resources;

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
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class OperationResource extends Resource
{
    protected static ?string $model = Operation::class;

    protected static ?string $navigationGroup = 'Setup Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
           Pages\ViewOperation::class,
           Pages\EditOperation::class
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Operation Details')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Operation Name')
                            ->icon('heroicon-o-bars-2'),

                        TextEntry::make('machines.name')
                            ->label('Machines')
                            ->lineClamp(3)
                            ->icon('heroicon-o-cog'),

                        TextEntry::make('price')
                            ->formatStateUsing(fn ($state) =>
                                number_format($state, 2, '.', ',') . ' MKD'),

                        ColorEntry::make('color'),

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
                        TextInput::make('name')->required()
                            ->suffixIcon('heroicon-o-bars-2')
                            ->suffixIconColor('primary'),
                        Select::make('Machines')
                            ->relationship('machines', 'name')
                            ->options(Machine::all()->pluck('name', 'id'))
                            ->label('Machines')
                            ->suffixIcon('heroicon-o-cog')
                            ->suffixIconColor('primary')
                            ->multiple(),
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->suffixIcon('heroicon-o-banknotes')
                            ->suffixIconColor('primary'),
                        ColorPicker::make('color')->required(),
                        Forms\Components\Textarea::make('description')
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
                    ->state(function (Operation $operation) {
                        $barsSvg = svg('heroicon-o-bars-2', 'w-5 h-5 mx-1')->toHtml();

                        return new HtmlString("
                            <div class=\"flex flex-row\">
                                {$barsSvg}<span class=\"text-sm text-gray-700 mx-1 font-semibold\">{$operation->name}</span>
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
                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ',') . ' MKD') // Format price to 2 decimal places and append a dollar sign
                    ->label('Price'),
                ColorColumn::make('color'),
                TextColumn::make('machines.name')
                    ->label('Machines')
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
            'index' => Pages\ListOperations::route('/'),
            'view' => Pages\ViewOperation::route('/{record}'),
            'create' => Pages\CreateOperation::route('/create'),
            'edit' => Pages\EditOperation::route('/{record}/edit'),
        ];
    }
}
