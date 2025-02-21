<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasMahasiswaResource\Pages;
use App\Filament\Resources\KelasMahasiswaResource\RelationManagers;
use App\Models\KelasMahasiswa;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KelasMahasiswaResource extends Resource
{
    protected static ?string $model = KelasMahasiswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Manajemen Gabung Kelas';
    protected static ?string $navigationLabel = 'Manajemen Gabung Kelas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kelas.nama_kelas')->label('Kelas'),
                Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => ucfirst($state)) // Mengubah status menjadi kapital
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'primary',   // Badge warna biru
                        'accepted' => 'success',  // Badge warna hijau
                        'rejected' => 'danger',   // Badge warna merah
                        default => 'gray',        // Warna default untuk status tidak dikenal
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Terima')->button()
                    ->color('success')
                    ->visible(fn(KelasMahasiswa $record) => $record->status === 'pending' || $record->status === 'rejected')
                    ->action(fn(KelasMahasiswa $record) => $record->update(['status' => 'accepted'])),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')->button()
                    ->color('danger')
                    ->visible(fn(KelasMahasiswa $record) => $record->status === 'pending' || $record->status === 'accepted')
                    ->action(fn(KelasMahasiswa $record) => $record->update(['status' => 'rejected'])),
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

    public static function canCreate(): bool
    {
        return false; // Menonaktifkan tombol Create
    }

    public static function canEdit($record): bool
    {
        return false; // Menonaktifkan tombol Edit
    }
    public static function canViewAny(): bool
    {

        return auth()->user()?->roles()->whereIn('name', ['admin', 'dosen'])->exists();
    }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->roles()->whereIn('name', ['admin', 'dosen'])->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelasMahasiswas::route('/'),
            // 'create' => Pages\CreateKelasMahasiswa::route('/create'),
            // 'edit' => Pages\EditKelasMahasiswa::route('/{record}/edit'),
        ];
    }
}
