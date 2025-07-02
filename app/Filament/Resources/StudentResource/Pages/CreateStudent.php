<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\CreateAndRedirectToIndex;
use App\Filament\Resources\StudentResource;
use App\Models\User;
use App\Models\Department;
use App\Models\Program;
use App\Models\Role;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateStudent extends CreateAndRedirectToIndex
{
    use CreateRecord\Concerns\HasWizard;
    protected static string $resource = StudentResource::class;

    protected function getSteps(): array
    {
        return [
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
                            TextInput::make('user.password')
                                ->label('Password')
                                ->password()
                                ->revealable()
                                ->default('celms')
                                ->required(),
                            TextInput::make('user.address')
                                ->label('Address')
                                ->required()
                                ->columnSpanFull(),
                        ])
                ]),
            Step::make('Program Assignment')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('department_id')
                                ->label('Department')
                                ->options(
                                    Department::all()->pluck('code', 'id')->map(function ($code, $id) {
                                        $dept = Department::find($id);
                                        return "{$dept->name} ({$code})";
                                    })
                                )
                                ->required()
                                ->afterStateUpdated(fn($set) => $set('program_id', null))
                                ->reactive(),
                            Select::make('program_id')
                                ->label('Program')
                                ->options(function ($get) {
                                    return Program::where('department_id', $get('department_id'))->pluck('name', 'id');
                                })
                                ->disabled(fn($get) => blank($get('department_id')))
                                ->required()
                                ->reactive()
                        ])
                ])
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create($data['user']);
        $user->assignRole(Role::STUDENT);
        $data['user_id'] = $user->id;
        unset($data['user']);
        return $data;
    }
}
