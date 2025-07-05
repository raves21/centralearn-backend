<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\CreateAndRedirectToIndex;
use App\Filament\Resources\AdminResource;
use App\Models\Role;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateAndRedirectToIndex
{
    protected static string $resource = AdminResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction()
        ];
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create($data['user']);
        $user->assignRole(Role::ADMIN);
        $data['user_id'] = $user->id;
        unset($data['user']);
        return $data;
    }
}
