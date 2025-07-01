<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user'] = $this->record->user->toArray();
        $data['department_id'] = $this->record->program->department->id;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $old = [
            'user' => $this->record->user->only(['first_name', 'last_name', 'email', 'address']),
            'department_id' => $this->record->program->department->id,
            'program_id' => $this->record->program->id,
        ];

        if ($old !== $data) {
            $user = User::find($this->record->user->id);
            $user->update($data['user']);
            $program = Program::find($data['program_id']);
            $user->program()->associate($program);
        }
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
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
                                    TextInput::make('user.address')
                                        ->label('Address')
                                        ->required()
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
                ])->columnSpanFull()
            ]);
    }
}
