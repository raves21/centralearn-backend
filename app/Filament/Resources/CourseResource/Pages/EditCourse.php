<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Models\Department;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['departments'] = $this->record->departments->pluck('id')->toArray();
        return $data;
    }

    protected function beforeSave()
    {
        $selectedDepts = $this->data['departments'];
        $course = $this->record;
        $course->departments()->sync($selectedDepts);
    }

    protected function getRedirectUrl(): ?string
    {
        return CourseResource::getUrl('view', ['record' => $this->record]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make()
                    ->columnSpanFull()
                    ->steps([
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
                            ])
                    ])
            ]);
    }
}
