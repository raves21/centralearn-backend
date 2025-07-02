<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Models\Role;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

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
                    return redirect(AdminResource::getUrl('index'));
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user'] = $this->record->user->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->record->user;
        $user->update($data['user']);
        return $data;
    }

    public function form(Form $form): Form
    {
        return $form
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
                            ->columnSpanFull()
                    ])
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
