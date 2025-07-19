<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class CourseEnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'courseEnrollments';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course')
            ->heading('Courses Enrolled')
            ->emptyStateHeading('Not enrolled to any course.')
            ->columns([
                TextColumn::make('course.name')
                    ->label('Course'),
                TextColumn::make('course.code')
                    ->label('Code'),
                TextColumn::make('semester.name')
                    ->label('Semester'),
                TextColumn::make('created_at')
                    ->label('Date Enrolled')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('course_enrollment')
                    ->label('Enroll to Course')
                    ->modalHeading('Enroll to Course')
                    ->modalWidth('2xl')
                    ->form([
                        Select::make('semester_id')
                            ->label('Semester')
                            ->options(Semester::orderBy('end_date')
                                ->get()
                                ->mapWithKeys(fn($sem) => [$sem->id => "{$sem->name} " . "(" . Carbon::parse($sem->start_date)->format('M j, Y') . " - " . Carbon::parse($sem->end_date)->format('M j, Y') . ")"]))
                            ->reactive()
                            ->native(false)
                            ->required()
                            ->afterStateUpdated(fn($set) => $set('course_id', null)),
                        Select::make('course_id')
                            ->label('Course')
                            ->options(function ($get) {
                                $student = $this->ownerRecord;
                                $assignedCoursesIds = $student->courseEnrollments()
                                    ->where('semester_id', $get('semester_id'))
                                    ->pluck('course_id');

                                $availableCourses = Course::whereNotIn('id', $assignedCoursesIds)
                                    ->whereHas('departments', function ($q) {
                                        $q->where('departments.id', $this->ownerRecord->program->department_id);
                                    })
                                    ->get()
                                    ->mapWithKeys(fn($course) => [$course->id => "{$course->name} ({$course->code})"]);
                                return $availableCourses;
                            })
                            ->reactive()
                            ->native(false)
                            ->required()
                            ->disabled(fn($get) => empty($get('semester_id')))
                            ->helperText(fn() => "Only courses under the student's department ({$this->ownerRecord->program->department->code})")
                    ])
                    ->mutateFormDataUsing(function ($data) {
                        $data['student_id'] = $this->ownerRecord->id;
                        return $data;
                    })
                    ->successNotificationTitle(function ($data) {
                        $courseCode = Course::find($data['course_id'])->code;
                        return "Enrolled to {$courseCode}.";
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading('View Course Enrollment'),
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Edit Course Enrollment')
                        ->modalWidth('2xl')
                        ->form([
                            Select::make('semester_id')
                                ->label('Semester')
                                ->options(Semester::orderByDesc('created_at')->get()
                                    ->mapWithKeys(fn($sem) => [$sem->id => "{$sem->name} " . "(" . Carbon::parse($sem->start_date)->format('M j, Y') . " - " . Carbon::parse($sem->end_date)->format('M j, Y') . ")"]))
                                ->reactive()
                                ->native(false)
                                ->required()
                                ->afterStateUpdated(fn($set) => $set('course_id', null)),
                            Select::make('course_id')
                                ->label('Course')
                                ->options(function ($get) {
                                    $student = $this->ownerRecord;
                                    $assignedCoursesIds = $student->courseEnrollments()
                                        ->where('semester_id', $get('semester_id'))
                                        ->pluck('course_id');

                                    $availableCourses = Course::whereNotIn('id', $assignedCoursesIds)
                                        ->whereHas('departments', function ($q) {
                                            $q->where('departments.id', $this->ownerRecord->program->department_id);
                                        })
                                        ->get()
                                        ->mapWithKeys(fn($course) => [$course->id => "{$course->name} ({$course->code})"]);
                                    return $availableCourses;
                                })
                                ->getOptionLabelUsing(function ($state) {
                                    $course = Course::find($state);
                                    return "{$course->name} ({$course->code})";
                                })
                                ->reactive()
                                ->native(false)
                                ->required()
                                ->disabled(fn($get) => empty($get('semester_id')))
                        ]),
                    Tables\Actions\DeleteAction::make()
                        ->label('Delete')
                        ->modalHeading('Delete course enrollment'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(2)
            ->schema([
                Section::make('Course')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('course.name')
                            ->label('Name'),
                        TextEntry::make('course.code')
                            ->label('Code'),
                        TextEntry::make('departments')
                            ->label('Department/s')
                            ->getStateUsing(fn($record) => implode(' / ', $record->course->departments()->pluck('code')->toArray())),
                    ]),
                Section::make('Semester')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('semester.name')
                            ->label('Name'),
                        TextEntry::make('semester.start_date')
                            ->label('Start Date')
                            ->formatStateUsing(fn($state) => Carbon::parse($state)->format('M j, Y')),
                        TextEntry::make('semester.end_date')
                            ->label('End Date')
                            ->formatStateUsing(fn($state) => Carbon::parse($state)->format('M j, Y')),
                    ])
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
