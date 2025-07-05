<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Models\CourseInstructorAssignment;
use App\Models\Department;
use App\Models\Instructor;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateCourse extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;
    protected static string $resource = CourseResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Course Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->rows(4)
                                ->maxLength(255),
                            FileUpload::make('image_path')
                                ->label('Image')
                                ->imageEditor()
                                ->image(),
                        ])
                ]),
            Step::make('Department')
                ->schema([
                    Select::make('departments')
                        ->label('Department/s')
                        ->helperText('Choose which department/s this course will belong')
                        ->multiple()
                        ->options(Department::all()->mapWithKeys(fn($dept) => [$dept->id => "{$dept->name} ({$dept->code})"]))
                        ->reactive()
                        ->native(false)
                        ->required(),
                ]),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return CourseResource::getUrl('index');
    }

    protected function afterCreate()
    {
        $selectedDepts = $this->data['departments'];

        if (!empty($selectedDepts)) {
            $this->record->departments()->attach($selectedDepts);
        }
    }
}
