<?php

namespace App\Filament\Resources\InstructorResource\Pages;

use App\Filament\Resources\InstructorResource;
use App\Filament\Resources\InstructorResource\RelationManagers\CourseAssignmentsRelationManager;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInstructor extends ViewRecord
{
    protected static string $resource = InstructorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            CourseAssignmentsRelationManager::class
        ];
    }
}
