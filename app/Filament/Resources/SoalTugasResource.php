<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoalTugasResource\Pages;
use App\Filament\Resources\SoalTugasResource\RelationManagers;
use App\Models\SoalTugas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SoalTugasResource extends Resource
{
    protected static ?string $model = SoalTugas::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('pertanyaan')->required()->label('Pertanyaan'),
                Forms\Components\TextInput::make('pilihan_a')->required()->label('Pilihan A'),
                Forms\Components\TextInput::make('pilihan_b')->required()->label('Pilihan B'),
                Forms\Components\TextInput::make('pilihan_c')->required()->label('Pilihan C'),
                Forms\Components\TextInput::make('pilihan_d')->required()->label('Pilihan D'),
                Forms\Components\TextInput::make('jawaban_benar')->required()->label('Jawaban Benar'),
                Forms\Components\Textarea::make('alasan')->nullable()->label('Alasan'),
                Forms\Components\Select::make('tugas_id')
                    ->options(\App\Models\Tugas::all()->pluck('judul', 'id'))
                    ->required()
                    ->label('Tugas'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pertanyaan')->label('Pertanyaan'),
                Tables\Columns\TextColumn::make('jawaban_benar')->label('Jawaban Benar'),
                Tables\Columns\TextColumn::make('tugas.judul')->label('Tugas'),

            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListSoalTugas::route('/'),
            'create' => Pages\CreateSoalTugas::route('/create'),
            'edit' => Pages\EditSoalTugas::route('/{record}/edit'),
        ];
    }
}
