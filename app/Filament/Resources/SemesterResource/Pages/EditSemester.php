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
        $latestSemEndDate = Semester::latest()->first()->end_date;
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        DatePicker::make('start_date')
                            ->required()
                            ->minDate(function () {
                                if (Semester::count() === 1) return null;
                                return Semester::latest()->first()->start_date;
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($set, $state, $get) {
                                $startDate = Carbon::parse($state);
                                $endDate = Carbon::parse($get('end_date'));
                                if ($startDate->greaterThanOrEqualTo($endDate)) {
                                    $set('end_date', null);
                                }
                            })
                            ->native(false),
                        DatePicker::make('end_date')
                            ->required()
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'You can only choose dates that come after start date.')
                            ->reactive()
                            ->disabled(fn($get) => empty($get('start_date')))
                            ->minDate(fn($get) => Carbon::parse($get('start_date'))->addDay(1))
                            ->native(false),
                    ])
            ]);
    }
}
