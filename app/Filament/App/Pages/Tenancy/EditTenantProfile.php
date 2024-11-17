<?php

namespace App\Filament\App\Pages\Tenancy;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;

class EditTenantProfile extends \Filament\Pages\Tenancy\EditTenantProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getLabel(): string
    {
        return __('Company Profile');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label(__('Save changes'))
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('address')
                    ->label(__('Company Address')),
                TextInput::make('phone')
                    ->label(__('Company Phone')),
                TextInput::make('email')
                    ->label(__('Company Email'))
                    ->email(),
                TextInput::make('website')
                    ->label(__('Company Website')),
            ]);
    }
}
