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
                TextColumn::make('departments')
                    ->label('Department/s')
                    ->getStateUsing(function ($record) {
                        $course = Course::find($record->course_id);
                        $deptCodes = $course->departments->pluck('code')->toArray();
                        $allDeptCodes = Department::all()->pluck('code')->toArray();
                        sort($deptCodes);
                        sort($allDeptCodes);
                        $commaSeparated = implode(' / ', $deptCodes);

                        $html = '';

                        if ($allDeptCodes === $deptCodes) {
                            $html = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-infinity-icon lucide-infinity">
                            <path d="M6 16c5 0 7-8 12-8a4 4 0 0 1 0 8c-5 0-7-8-12-8a4 4 0 1 0 0 8"/></svg>';
                        } else {
                            $html = "<div style='display: flex; place-items: center; gap: 4px; flex-wrap: wrap; max-width: 140px;'>" . $commaSeparated . "</div>";
                        }

                        return new HtmlString($html);
                    }),
                TextColumn::make('semester.name')
                    ->label('Semester')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('course_assignment')
                    ->label('Assign to Course')
                    ->modalHeading('Assign to Course')
                    ->form(
                        [Grid::make(2)
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
                                    ->helperText('Only courses under the instructor\'s department.')
                                    ->options(function ($get) {
                                        $instructor = $this->ownerRecord;
                                        $assignedCoursesIds = $instructor->courseAssignments()
                                            ->where('semester_id', $get('semester_id'))
                                            ->pluck('course_id');

                                        $availableCourses = Course::whereNotIn('id', $assignedCoursesIds)
                                            ->whereHas('departments', function ($q) {
                                                $q->where('departments.id', $this->ownerRecord->department->id);
                                            })
                                            ->get()
                                            ->mapWithKeys(fn($course) => [$course->id => "{$course->name} ({$course->code})"]);
                                        return $availableCourses;
                                    })
                                    ->reactive()
                                    ->disabled(fn($get) => empty($get('semester_id')))
                                    ->native(false)
                                    ->required()
                            ])]
                    )
                    ->mutateFormDataUsing(function ($data) {
                        $data['instructor_id'] = $this->ownerRecord->id;
                        return $data;
                    })
                    ->action(function ($data) {
                        try {
                            CourseInstructorAssignment::create($data);
                            $courseCode = Course::find($data['course_id'])->code;
                            Notification::make()
                                ->title("Assigned to " . $courseCode . ".")
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('An error occured.')
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading('View Course Assignment'),
                    Tables\Actions\EditAction::make('Edit')
                        ->icon('heroicon-o-pencil-square')
                        ->modalHeading('Edit Course Assignment')
                        ->form([
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
                                        ->helperText('Only courses under the instructor\'s department.')
                                        ->options(function ($get) {
                                            $instructor = $this->ownerRecord;
                                            $assignedCoursesIds = $instructor->courseAssignments()
                                                ->where('semester_id', $get('semester_id'))
                                                ->pluck('course_id');

                                            $availableCourses = Course::whereNotIn('id', $assignedCoursesIds)
                                                ->get()
                                                ->mapWithKeys(fn($course) => [$course->id => "{$course->name} ({$course->code})"]);
                                            return $availableCourses;
                                        })
                                        ->getOptionLabelUsing(function ($value) {
                                            $course = Course::find($value);
                                            return $course ? "{$course->name} ({$course->code})" : null;
                                        })
                                        ->reactive()
                                        ->disabled(fn($get) => empty($get('semester_id')))
                                        ->native(false)
                                        ->required()
                                ])
                        ]),
                    Tables\Actions\DeleteAction::make()
                        ->label('Unassign')
                        ->modalHeading('Unassign Course')
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
                        TextEntry::make('course_name')
                            ->label('Name')
                            ->getStateUsing(fn($record) => $record->course->name),
                        TextEntry::make('course_code')
                            ->label('Code')
                            ->getStateUsing(fn($record) => $record->course->code),
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
