<?php

namespace App\Filament\Resources\InstructorResource\RelationManagers;

use App\Models\Course;
use App\Models\CourseInstructorAssignment;
use App\Models\Department;
use App\Models\Semester;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Throwable;

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
            ->emptyStateHeading('Not assigned to any course.')
            ->heading('Courses Assigned')
            ->columns([
                TextColumn::make('course.name')
                    ->label('Name'),
                TextColumn::make('course.code')
                    ->label('Code'),
                TextColumn::make('semester.name')
                    ->label('Semester'),
                TextColumn::make('created_at')
                    ->label('Date Assigned')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('semester_id')
                    ->label('Semester')
                    ->native(false)
                    ->relationship('semester', 'name')
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->createAnother(false)
                    ->label('Assign to Course')
                    ->modalHeading('Assign to Course')
                    ->modalWidth('2xl')
                    ->form([
                        Select::make('semester_id')
                            ->label('Semester')
                            ->options(Semester::orderBy('end_date')
                                ->get()
                                ->mapWithKeys(fn($sem) => [$sem->id => "{$sem->name} " . "(" . Carbon::parse($sem->start_date)->format('M j, Y') . " - " . Carbon::parse($sem->end_date)->format('M j, Y') . ")"]))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($set) => $set('course_id', null))
                            ->native(false),
                        Select::make('course_id')
                            ->label('Course')
                            ->helperText('Only courses under the instructor\'s department ' . "({$this->ownerRecord->department->code})")
                            ->options(function ($get) {
                                $instructor = $this->ownerRecord;
                                $assignedCoursesIds = $instructor->courseAssignments()
                                    ->where('semester_id', $get('semester_id'))
                                    ->pluck('course_id');

                                $availableCourses = Course::whereNotIn('id', $assignedCoursesIds)
                                    ->whereHas('departments', function ($q) {
                                        $q->where('departments.id', $this->ownerRecord->department_id);
                                    })
                                    ->get()
                                    ->mapWithKeys(fn($course) => [$course->id => "{$course->name} ({$course->code})"]);
                                return $availableCourses;
                            })
                            ->reactive()
                            ->disabled(fn($get) => empty($get('semester_id')))
                            ->native(false)
                            ->required()
                    ])
                    ->mutateFormDataUsing(function ($data) {
                        $data['instructor_id'] = $this->ownerRecord->id;
                        return $data;
                    })
                    ->successNotificationTitle(function ($data) {
                        $courseCode = Course::find($data['course_id'])->code;
                        return "Assigned to {$courseCode}.";
                    })
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading('View Course Assignment'),
                    Tables\Actions\EditAction::make()
                        ->modalWidth('2xl')
                        ->modalHeading('Edit Course Assignment')
                        ->form([
                            Select::make('semester_id')
                                ->label('Semester')
                                ->options(Semester::orderBy('end_date')->get()->pluck('name', 'id'))
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn($set) => $set('course_id', null))
                                ->native(false),
                            Select::make('course_id')
                                ->label('Course')
                                ->helperText("Only courses under the instructor's department. ({$this->ownerRecord->department->code})")
                                ->options(function ($get) {
                                    $instructor = $this->ownerRecord;
                                    $assignedCoursesIds = $instructor->courseAssignments()
                                        ->where('semester_id', $get('semester_id'))
                                        ->pluck('course_id');

                                    $availableCourses = Course::whereNotIn('id', $assignedCoursesIds)
                                        ->whereHas('departments', function ($q) {
                                            $q->where('departments.id', $this->ownerRecord->department_id);
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
                                ->disabled(fn($get) => empty($get('semester_id')))
                                ->native(false)
                                ->required()
                        ]),
                    Tables\Actions\DeleteAction::make()
                        ->label('Delete')
                        ->modalHeading('Delete course assignment')
                        ->successNotification(function () {
                            return Notification::make()->success()->title('Course unassiged.')->send();
                        })
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
                    ->schema([
                        TextEntry::make('course.name')
                            ->label('Name'),
                        TextEntry::make('course.code')
                            ->label('Code'),
                        TextEntry::make('departments')
                            ->label('Department/s')
                            ->getStateUsing(fn($record) => implode(' / ', $record->course->departments->pluck('code')->toArray()))
                    ])
                    ->columnSpan(1),
                Section::make('Semester')
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
                    ->columnSpan(1)
            ]);
    }
}
