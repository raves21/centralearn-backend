<?php

namespace App\Filament\Resources\SemesterResource\Pages;

use App\Filament\CreateAndRedirectToIndex;
use App\Filament\Resources\SemesterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSemester extends CreateAndRedirectToIndex
{
    protected static string $resource = SemesterResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction()
        ];
    }
}
