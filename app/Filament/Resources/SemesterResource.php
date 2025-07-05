<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SemesterResource\Pages;
use App\Models\Semester;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class SemesterResource extends Resource
{
    protected static ?string $model = Semester::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Institution';

    public static function form(Form $form): Form
    {
        $latestSemEndDate = Carbon::parse(Semester::latest()->first()->end_date);
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->helperText(function () use ($latestSemEndDate) {
                                return new HtmlString(
                                    "<div>" .
                                        "<p>You can only choose dates that come after the latest semester's end date.</p>" .
                                        '<p style="color: red; margin-top: 4px;">(' . $latestSemEndDate->format('F j, Y') . " and up)</p>" .
                                        "</div>"
                                );
                            })
                            ->minDate(function () use ($latestSemEndDate) {
                                return $latestSemEndDate->addDay(1);
                            })
                            ->reactive()
                            ->afterStateUpdated(fn($set) => $set('end_date', null))
                            ->native(false),
                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->helperText('You can only choose dates that come after start date.')
                            ->reactive()
                            ->disabled(fn($get) => empty($get('start_date')))
                            ->minDate(fn($get) => Carbon::parse($get('start_date'))->addDay(1))
                            ->native(false),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Semester::orderByDesc('end_date'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('start_date')->date(),
                        TextEntry::make('end_date')->date(),
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
            'index' => Pages\ListSemesters::route('/'),
            'create' => Pages\CreateSemester::route('/create'),
            'edit' => Pages\EditSemester::route('/{record}/edit'),
            'view' => Pages\ViewSemester::route('/{record}'),
        ];
    }
}
