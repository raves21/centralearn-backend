<?php

namespace App\Filament\Resources\SemesterResource\Pages;

use App\Filament\Resources\SemesterResource;
use App\Models\Semester;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;

class EditSemester extends EditRecord
{
    protected static string $resource = SemesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn() => Semester::count() !== 1)
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return SemesterResource::getUrl('view', ['record' => $this->record]);
    }

    public function form(Form $form): Form
    {
        $record = $this->record;

        $prevSemEndDateQuery = Semester::orderByDesc('end_date')->where('end_date', '<', $record->start_date)->first() ?? null;
        $prevSemEndDate = $prevSemEndDateQuery ? Carbon::parse($prevSemEndDateQuery->end_date) : null;

        $nextSemStartDateQuery = Semester::orderBy('end_date')->where('start_date', '>', $record->end_date)->first() ?? null;
        $nextSemStartDate = $nextSemStartDateQuery ? Carbon::parse($nextSemStartDateQuery->start_date) : null;

        return $form
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->required()
                    ->helperText(function () use ($prevSemEndDate, $nextSemStartDate) {
                        if ($prevSemEndDate && $nextSemStartDate) {
                            return new HtmlString(
                                "<div>" .
                                    "<p>You can only choose dates between the previous semester's end date (if any) and the next semester's start date (if any).</p>" .
                                    '<p style="color: red; margin-top: 4px;">(Between ' . $prevSemEndDate->format('F j, Y') . " and " . $nextSemStartDate->format('F j, Y') . ")</p>" .
                                    "</div>"
                            );
                        } else if ($prevSemEndDate && !$nextSemStartDate) {
                            return new HtmlString(
                                "<div>" .
                                    "<p>You can only choose dates between the previous semester's end date (if any) and the next semester's start date (if any).</p>" .
                                    '<p style="color: red; margin-top: 4px;">(' . $prevSemEndDate->format('F j, Y')  . " onwards)</p>" .
                                    "</div>"
                            );
                        } else {
                            return new HtmlString(
                                "<div>" .
                                    "<p>You can only choose dates between the previous semester's end date (if any) and the next semester's start date (if any).</p>" .
                                    '<p style="color: red; margin-top: 4px;">(From any date up to ' . $nextSemStartDate->format('F j, Y')  . ")</p>" .
                                    "</div>"
                            );
                        }
                    })
                    ->minDate($prevSemEndDate ? $prevSemEndDate->addDay(1) : null)
                    ->maxDate($nextSemStartDate ? $nextSemStartDate->subDay(1) : null)
                    ->reactive()
                    ->afterStateUpdated(fn($set) => $set('end_date', null))
                    ->native(false),
                DatePicker::make('end_date')
                    ->required()
                    ->helperText('You can only choose dates that come after start date.')
                    ->reactive()
                    ->disabled(fn($get) => empty($get('start_date')))
                    ->minDate(fn($get) => Carbon::parse($get('start_date'))->addDay(1))
                    ->maxDate($nextSemStartDate ? $nextSemStartDate : null)
                    ->native(false),
            ]);
    }
}
