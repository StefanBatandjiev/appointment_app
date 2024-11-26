<?php

namespace App\Filament\App\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Facades\Filament;
use Filament\Forms;
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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            \App\Filament\App\Resources\ClientResource\Pages\ViewClient::class,
            \App\Filament\App\Resources\ClientResource\Pages\EditClient::class,
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('Client Details'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Client Name'))
                            ->icon('heroicon-o-user'),

                        TextEntry::make('telephone')
                            ->label(__('Client Telephone'))
                            ->default('N/A')
                            ->icon('heroicon-o-phone'),

                        TextEntry::make('email')
                            ->label(__('Client Email'))
                            ->default('N/A')
                            ->icon('heroicon-o-envelope'),
                    ])
                    ->columns(3)
                    ->columnSpan(2),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Client Name'))
                            ->suffixIcon('heroicon-o-user')
                            ->suffixIconColor('primary')
                            ->required(),

                        TextInput::make('email')
                            ->label(__('Client Email'))
                            ->email()
                            ->suffixIcon('heroicon-o-envelope')
                            ->suffixIconColor('primary')
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                                return $rule->where('tenant_id', Filament::getTenant()->id);
                            }),

                        TextInput::make('telephone')
                            ->label(__('Client Telephone'))
                            ->nullable()
                            ->suffixIcon('heroicon-o-phone')
                            ->suffixIconColor('primary')
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                                return $rule->where('tenant_id', Filament::getTenant()->id);
                            }),
                    ])->columnSpan(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name and Last Name'))
                    ->state(function (Client $client) {
                        $userSvg = svg('heroicon-o-user', 'w-5 h-5 mx-1')->toHtml();

                        return new HtmlString("
                            <div class=\"flex flex-row\">
                                {$userSvg}<span class=\"text-sm text-gray-700 mx-1 font-semibold\">{$client->name}</span>
                             </div>
                        ");
                    }),
                TextColumn::make('email')
                    ->translateLabel()
                    ->state(function (Client $client) {
                        $emailSvg = svg('heroicon-o-envelope', 'w-5 h-5 mx-1')->toHtml();
                        $email = $client->email ?? 'N/A';

                        return new HtmlString("
                            <div class=\"flex flex-row\">
                                {$emailSvg}<span class=\"text-sm text-gray-700 mx-1 font-semibold\">{$email}</span>
                             </div>
                        ");
                    }),
                TextColumn::make('telephone')
                    ->translateLabel()
                    ->state(function (Client $client) {
                        $telephoneSvg = svg('heroicon-o-phone', 'w-5 h-5 mx-1')->toHtml();
                        $telephone = $client->telephone ?? 'N/A';

                        return new HtmlString("
                            <div class=\"flex flex-row\">
                                {$telephoneSvg}<span class=\"text-sm text-gray-700 mx-1 font-semibold\">{$telephone}</span>
                             </div>
                        ");
                    }),
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
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
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
            'index' => \App\Filament\App\Resources\ClientResource\Pages\ListClients::route('/'),
            'create' => \App\Filament\App\Resources\ClientResource\Pages\CreateClient::route('/create'),
            'view' => \App\Filament\App\Resources\ClientResource\Pages\ViewClient::route('/{record}'),
            'edit' => \App\Filament\App\Resources\ClientResource\Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Reservation Management');
    }

    public static function getModelLabel(): string
    {
        return __('Client');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Clients');
    }
}
