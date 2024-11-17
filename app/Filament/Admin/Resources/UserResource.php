<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'User Management';

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
                            ->visible(fn($livewire) => $livewire instanceof \App\Filament\Admin\Resources\UserResource\Pages\CreateUser)
                            ->rule(Password::default()),
                        TextInput::make('telephone')
                            ->label(__('User Telephone'))
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('tenants')
                            ->relationship('tenants', 'name')
                            ->label(__('Tenants'))
                            ->multiple()
                            ->options(Tenant::all()->pluck('name', 'id')),
                        Toggle::make('is_admin')
                            ->onColor('success')
                            ->offColor('danger')
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
                TextColumn::make('tenants.name'),
                TextColumn::make('is_admin')->label('Admin')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
