<?php

namespace App\Filament\App\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-s-users';

    protected static ?string $tenantOwnershipRelationshipName = 'tenants';

    public static function getNavigationGroup(): string
    {
        return __('User Management');
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Users');
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('User Details'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('User Name'))
                        ->required(),
                    TextInput::make('email')
                        ->label(__('User Email'))
                        ->email()->required()->unique(ignoreRecord: true),
                    TextInput::make('password')
                        ->label(__('User Password'))
                        ->required()->password()
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->visible(fn($livewire) => $livewire instanceof \App\Filament\App\Resources\UserResource\Pages\CreateUser)
                        ->rule(Password::default()),
                    TextInput::make('telephone')
                        ->label(__('User Telephone'))
                        ->unique(ignoreRecord: true),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('User Name')),
                TextColumn::make('email')
                    ->label(__('User Email')),
                TextColumn::make('telephone')
                    ->label(__('User Telephone')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => Auth::user()->id === $record->id),
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
            'index' => \App\Filament\App\Resources\UserResource\Pages\ListUsers::route('/'),
            'create' => \App\Filament\App\Resources\UserResource\Pages\CreateUser::route('/create'),
            'edit' => \App\Filament\App\Resources\UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
