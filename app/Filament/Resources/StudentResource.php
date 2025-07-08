<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Filament\Resources\StudentResource\RelationManagers\CourseEnrollmentsRelationManager;
use App\Models\Program;
use App\Models\Student;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Student::orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('user.first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('program.code')
                    ->label('Program')
                    ->searchable(),
                TextColumn::make('program.department.code')
                    ->label('Department')
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
                SelectFilter::make('program_id')
                    ->label('Program')
                    ->options(Program::all()->mapWithKeys(fn($program) => [$program->id => "{$program->code} ({$program->name})"]))
                    ->searchable()
                    ->placeholder('All Programs'),
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
                            ->label('First Name'),
                        TextEntry::make('user.address')
                            ->label('Address'),
                        TextEntry::make('user.email')
                            ->label('Email')
                    ]),
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('program.department_id')
                            ->formatStateUsing(fn($record) => "{$record->program->department->name} ({$record->program->department->code})"),
                        TextEntry::make('program.name')
                            ->formatStateUsing(fn($record) => "{$record->program->name} ({$record->program->code})")
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CourseEnrollmentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'view' => Pages\ViewStudent::route('/{record}'),
        ];
    }
}
