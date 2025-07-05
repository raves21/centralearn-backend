<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Filament\Resources\AdminResource\RelationManagers;
use App\Models\Admin;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid as ComponentsGrid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
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
                TextInput::make('user.address')
                    ->label('Address')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Admin::orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('user.first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('job_title')
                    ->label('Job Title')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Date Enrolled')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('job_title')
                    ->label('Job Title')
                    ->options(Admin::distinct()->pluck('job_title', 'job_title'))
                    ->searchable()
                    ->placeholder('Job Title'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.first_name')
                            ->label('First Name'),
                        TextEntry::make('user.last_name')
                            ->label('Last Name'),
                        TextEntry::make('user.address')
                            ->label('Address'),
                        TextEntry::make('user.email')
                            ->label('Email'),
                        TextEntry::make('job_title')
                            ->label('Job Title')
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
            'view' => Pages\ViewAdmin::route('/{record}'),
        ];
    }
}
