<?php

namespace App\Filament\Resources\InstructorResource\Pages;

use App\Filament\CreateAndRedirectToIndex;
use App\Filament\Resources\InstructorResource;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;

class CreateInstructor extends CreateAndRedirectToIndex
{
    use CreateRecord\Concerns\HasWizard;
    protected static string $resource = InstructorResource::class;

    protected function getSteps(): array
    {
        return [
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
                    TextInput::make('user.password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->required(),
                    TextInput::make('job_title')
                        ->label('Job Title')
                        ->required(),
                    Select::make('user.is_admin')
                        ->native(false)
                        ->visible(fn() => auth()->user()->hasRole(Role::SUPERADMIN))
                        ->label('Give Admin rights')
                        ->options([false => 'No', true => 'Yes',])
                        ->default(false),
                    TextInput::make('user.address')
                        ->label('Address')
                        ->required()
                        ->columnSpan(fn() => auth()->user()->hasRole(Role::SUPERADMIN) ? 'full' : 1)
                ]),
            Step::make('Department')
                ->schema([
                    Select::make('department_id')
                        ->native(false)
                        ->label('Department')
                        ->options(Department::all()->mapWithKeys(fn($dept) => [$dept->id => "{$dept->name} ({$dept->code})"]))
                        ->required()
                ])
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create($data['user']);
        $data['user_id'] = $user->id;
        if ($data['user']['is_admin']) {
            $user->assignRole([Role::INSTRUCTOR, Role::ADMIN]);
        }
        unset($data['user']);
        return $data;
    }
}
