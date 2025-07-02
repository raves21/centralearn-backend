<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstructorResource\Pages;
use App\Models\Department;
use App\Models\Instructor;
use App\Models\Role;
use Filament\Forms\Components\Placeholder;
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
use Illuminate\Support\Facades\DB;

class InstructorResource extends Resource
{
    protected static ?string $model = Instructor::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Instructor::orderBy('created_at', 'desc'))
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.code')
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
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::all()
                        ->mapWithKeys(fn($dept) => [$dept->id => "{$dept->code} ({$dept->name})"]))
                    ->searchable(),
                SelectFilter::make('job_title')
                    ->label('Job Title')
                    ->options(DB::table('instructors')
                        ->distinct()
                        ->pluck('job_title')
                        ->mapWithKeys(fn($jt) => [$jt => $jt])
                        ->toArray())
                    ->searchable()
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
                Grid::make(2)
                    ->schema([
                        Section::make('User Details')
                            ->schema([
                                TextEntry::make('user.first_name')
                                    ->label('First Name'),
                                TextEntry::make('user.last_name')
                                    ->label('Last Name'),
                                TextEntry::make('is_admin')
                                    ->label('Has Admin rights')
                                    ->badge()
                                    ->getStateUsing(function ($record) {
                                        return $record->user->hasRole(Role::ADMIN) ? "Yes" : "No";
                                    })
                                    ->color(fn(string $state) => match ($state) {
                                        'Yes' => 'success',
                                        'No' => 'warning'
                                    }),
                                TextEntry::make('user.address')
                                    ->label('Address'),
                                TextEntry::make('job_title')
                                    ->label('Job Title'),
                                TextEntry::make('user.email')
                                    ->label('Email'),
                            ])->columnSpan(1),
                        Section::make('Department Assignment')
                            ->schema([
                                TextEntry::make('department.name')
                                    ->label('Department'),
                            ])->columnSpan(1),
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
            'index' => Pages\ListInstructors::route('/'),
            'create' => Pages\CreateInstructor::route('/create'),
            'edit' => Pages\EditInstructor::route('/{record}/edit'),
            'view' => Pages\ViewInstructor::route('/{record}'),
        ];
    }
}
