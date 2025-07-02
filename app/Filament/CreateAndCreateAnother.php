<?php

namespace App\Filament;

use Filament\Resources\Pages\CreateRecord;

class CreateAndCreateAnother extends CreateRecord
{

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('create');
    }
}
