<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 4;

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->question;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['question', 'answer'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Question' => $record->question,
            'Answer' => $record->answer,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('edit', ['record' => $record]);
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'created_at';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Frequently Asked Question Details')
                ->description('Manage the question and answer for the FAQ.')
                ->aside()
                ->schema([
                    Forms\Components\TextInput::make('question')
                        ->required()
                        ->placeholder('Enter the question')
                        ->translatable(),

                    Forms\Components\Textarea::make('answer')
                        ->required()
                        ->placeholder('Provide the answer')
                        ->columnSpanFull()
                        ->translatable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn (Model $record) => $record->question),
                Tables\Columns\TextColumn::make('answer')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn (Model $record) => $record->answer),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
