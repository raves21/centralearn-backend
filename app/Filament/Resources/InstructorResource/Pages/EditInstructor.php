<?php

namespace App\Filament\Resources\InstructorResource\Pages;

use App\Filament\Resources\InstructorResource;
use App\Models\Department;
use App\Models\Instructor;
use App\Models\Role;
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
use Illuminate\Support\Facades\Hash;

class EditInstructor extends EditRecord
{
    protected static string $resource = InstructorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update-password')
                ->visible(fn() => auth()->user()->hasRole(Role::SUPERADMIN))
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
                    $user->password = Hash::make($data['new_password']);
                    $user->save();
                    Notification::make()
                        ->title('Password updated successfully.')
                        ->success()
                        ->send();
                    return redirect(InstructorResource::getUrl('index'));
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user'] = [...$this->record->user->toArray(), 'is_admin' => $this->record->user->hasRole(Role::ADMIN)];
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->record->user;

        if ($data['user']['is_admin']) {
            $user->assignRole([Role::INSTRUCTOR, Role::ADMIN]);
        } else {
            $user->removeRole(Role::ADMIN);
        }

        $user->update($data['user']);
        $selectedDept = Department::find($data['department_id']);
        $instructor = $this->record->user->instructor;
        $instructor->department()->associate($selectedDept);
        $instructor->save();
        return $data;
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
                        ->schema([
                            Grid::make(2)
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
                                    TextInput::make('job_title')
                                        ->label('Job Title')
                                        ->required(),
                                    TextInput::make('user.address')
                                        ->label('Address')
                                        ->required()
                                        ->columnSpan(fn() => auth()->user()->hasRole(Role::SUPERADMIN) ? 1 : 'full'),
                                    Select::make('user.is_admin')
                                        ->visible(fn() => auth()->user()->hasRole(Role::SUPERADMIN))
                                        ->label('Give Admin rights')
                                        ->options([true => 'Yes', false => 'No'])
                                        ->default(false),
                                ])
                        ]),
                    Step::make('Department Assignment')
                        ->schema([
                            Select::make('department_id')
                                ->label('Department')
                                ->options(
                                    Department::all()->mapWithKeys(fn($dept) => [$dept->id => "{$dept->code} ({$dept->name})"])
                                )
                                ->required()
                        ])
                ])->columnSpanFull()
            ]);
    }
}
