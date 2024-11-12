<?php

namespace App\Filament\App\Resources\UserResource\Pages;

use App\Filament\App\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['new_password'])) {
            $data['password'] = Hash::make($data['new_password']);
            session()->put([
                'password_hash_web' => $data['password']
            ]);

        }

        unset($data['new_password'], $data['new_password_confirmation']);

        return $data;
    }
}
