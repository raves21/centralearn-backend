<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\CreateAndRedirectToIndex;
use App\Filament\Resources\DepartmentResource;
use App\Models\Department;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateDepartment extends CreateAndRedirectToIndex
{
    protected static string $resource = DepartmentResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction()
        ];
    }
}
