<?php

namespace App\Filament\Resources\InstructorResource\RelationManagers;

use App\Models\Course;
use App\Models\CourseInstructorAssignment;
use App\Models\Semester;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'courseAssignments';

    public function form(Form $form): Form
    {
        return $form;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.name')
                    ->label('Name'),
                TextColumn::make('course.code')
                    ->label('Code'),
                TextColumn::make('semester')
                    ->getStateUsing(fn($record) => "{$record->semester->name}" . " (" . Carbon::parse($record->semester->start_date)->format('F j, Y') . " - " . Carbon::parse($record->semester->end_date)->format('F j, Y') . ")")
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('course_assignment')
                    ->label('Add Course Assignment')
                    ->modalHeading('Assign to Course')
                    ->modalWidth('2xl')
                    ->form(
                        [
                            Grid::make(2)
                                ->schema([
                                    Select::make('semester_id')
                                        ->label('Semester')
                                        ->options(Semester::orderBy('end_date')->get()->pluck('name', 'id'))
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn($set) => $set('course_id', null))
                                        ->native(false),
                                    Select::make('course_id')
                                        ->label('Course')
                                        ->options(fn() => Course::all()->pluck('name', 'id'))
                                        ->reactive()
                                        ->disabled(fn($get) => empty($get('semester_id')))
                                        ->native(false)
                                        ->required()
                                ])
                        ]
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Course Assignment')
                    ->modalWidth('lg')
                    ->form([
                        Select::make('course_id')
                            ->native(false)
                            ->columnSpanFull()
                            ->label('Course')
                            ->options(fn() => Course::all()->pluck('name', 'code'))
                            ->reactive()
                            ->required()
                    ]),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
