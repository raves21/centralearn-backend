<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\CreateAndRedirectToIndex;
use App\Filament\Resources\ProgramResource;
use App\Models\Department;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateProgram extends CreateAndRedirectToIndex
{
    use CreateRecord\Concerns\HasWizard;
    protected static string $resource = ProgramResource::class;

    public function getSteps(): array
    {
        return [
            Step::make('Program Details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(255),
                    FileUpload::make('image_path')
                        ->label('Image')
                        ->image(),
                ]),
            Step::make('Department')
                ->schema([
                    Select::make('department_id')
                        ->native(false)
                        ->label('Department')
                        ->options(Department::all()->mapWithKeys(fn($dept) => [$dept->id => "{$dept->name} ($dept->code)"]))
                        ->required()
                ])
        ];
    }
}
