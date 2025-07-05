<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Department;
use App\Models\Program;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update-password')
                ->visible(fn() => auth()->user()->hasRole([Role::ADMIN, Role::SUPERADMIN]))
                ->label('Update Password')
                ->form([
                    TextInput::make('new_password')
                        ->label('New Password')
                        ->password()
                        ->required()
                        ->autocomplete('off')
                        ->revealable()
                ])
                ->modalHeading('Password Update')
                ->modalWidth('lg')
                ->action(function ($data) {
                    $user = User::find($this->record->user->id);
                    $user->password = $data['new_password'];
                    $user->save();

                    Notification::make()
                        ->title('Password updated successfully.')
                        ->success()
                        ->send();

                    return redirect(StudentResource::getUrl('index'));
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user'] = $this->record->user->toArray();
        $data['department_id'] = $this->record->program->department->id;
        return $data;
    }

    protected function beforeSave()
    {
        $data = $this->data;
        $user = $this->record->user;

        $user->update($data['user']);
        $selectedProgram = Program::find($data['program_id']);
        $student = $this->record->user->student;
        $student->program()->associate($selectedProgram);
        $student->save();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('User Details')
                        ->columns(2)
                        ->schema([
                            TextInput::make('user.first_name')
                                ->label('First Name')
                                ->required(),
                            TextInput::make('user.last_name')
                                ->label('Last Name')
                                ->required(),
                            TextInput::make('user.email')
                                ->label('Email')
                                ->email()
                                ->required(),
                            TextInput::make('user.address')
                                ->label('Address')
                                ->required()
                        ]),
                    Step::make('Program')
                        ->columns(2)
                        ->schema([
                            Select::make('department_id')
                                ->label('Department')
                                ->options(Department::all()->mapWithKeys(fn($dept) => [$dept->id => "{$dept->name} ({$dept->code})"]))
                                ->native(false)
                                ->required()
                                ->afterStateUpdated(fn($set) => $set('program_id', null))
                                ->reactive(),
                            Select::make('program_id')
                                ->label('Program')
                                ->options(fn($get) => Program::where('department_id', $get('department_id'))->pluck('name', 'id'))
                                ->native(false)
                                ->disabled(fn($get) => blank($get('department_id')))
                                ->required()
                                ->reactive()
                        ])
                ])->columnSpanFull()
            ]);
    }
}
