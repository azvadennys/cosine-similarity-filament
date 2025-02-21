<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages;
use App\Filament\Resources\KelasResource\RelationManagers;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;
    protected static ?string $modelLabel = 'Manajemen Kelas';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_kelas')->required(),
                Forms\Components\Textarea::make('deskripsi'),
                Forms\Components\Select::make('dosen_id')
                    ->label('Dosen')
                    ->options(User::role('dosen')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('nama_kelas')->searchable(),
                Tables\Columns\TextColumn::make('deskripsi')
                    ->limit(50) // Membatasi teks hingga 50 karakter
                    ->tooltip(fn($record) => $record->deskripsi), // Tooltip untuk melihat teks leng
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('request_join')
                    ->label(
                        fn($record) =>
                        // Tampilkan status jika sudah request, atau 'Request Join' jika belum
                        KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
                            ->exists()
                            ? ucfirst(KelasMahasiswa::where('kelas_id', $record->id)
                                ->where('mahasiswa_id', auth()->id())
                                ->first()->status)
                            : 'Request Join'
                    )
                    ->color(
                        fn($record) =>
                        match (optional(KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
                            ->first())->status) {
                            'pending' => 'warning',    // Warna kuning untuk pending
                            'accepted' => 'success',   // Warna hijau untuk accepted
                            'rejected' => 'danger',    // Warna merah untuk rejected
                            default => 'primary',      // Warna biru untuk tombol Request Join
                        }
                    )
                    ->icon(
                        fn($record) =>
                        match (optional(KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
                            ->first())->status) {
                            'pending' => 'heroicon-o-clock',
                            'accepted' => 'heroicon-o-check-circle',
                            'rejected' => 'heroicon-o-x-circle',
                            default => 'heroicon-o-paper-airplane', // Default icon for Request Join
                        }
                    )->button()
                    ->visible(fn() => auth()->user()?->hasRole('mahasiswa')) // Hanya mahasiswa yang bisa request join
                    ->disabled(
                        fn($record) =>
                        // Nonaktifkan tombol jika user sudah mengirim request
                        KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
                            ->exists()
                    )
                    ->action(function ($record) {
                        // Proses Request Join
                        KelasMahasiswa::create([
                            'kelas_id' => $record->id,
                            'mahasiswa_id' => auth()->id(),
                            'status' => 'pending',
                        ]);

                        Notification::make()
                            ->title('Request berhasil dikirim')
                            ->body("Permintaan untuk bergabung ke {$record->nama_kelas} telah dikirim.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(), // Konfirmasi sebelum mengirim request

                Tables\Actions\ViewAction::make()
                    ->visible(
                        fn($record) =>
                        auth()->user()?->hasRole('admin') || // Admin bisa melihat semua
                            $record->dosen_id === auth()->id() || // Dosen hanya bisa melihat kelasnya sendiri
                            KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
                            ->where('status', 'accepted')
                            ->exists() // Mahasiswa hanya bisa melihat jika sudah accepted
                    )->button(),
                // Edit dan Delete hanya untuk dosen yang memiliki kelas atau admin
                Tables\Actions\EditAction::make()
                    ->visible(
                        fn($record) =>
                        auth()->user()?->hasRole('admin') || $record->dosen_id === auth()->id()
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(
                        fn($record) =>
                        auth()->user()?->hasRole('admin') || $record->dosen_id === auth()->id()
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->authorize(
                        fn() =>
                        auth()->user()?->hasAnyRole(['admin', 'dosen']) // Admin dan Dosen punya akses
                    )
                        ->before(function ($records) {
                            $user = auth()->user();

                            // Filter records jika user adalah dosen
                            if ($user->hasRole('dosen')) {
                                $records = $records->filter(fn($record) => $record->dosen_id === $user->id);
                            }

                            return $records;
                        }),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Jika user adalah dosen, tampilkan hanya kelas yang dia punya
        if (auth()->user()?->hasRole('dosen')) {
            return $query->where('dosen_id', auth()->id());
        }

        // Untuk admin atau role lainnya, tampilkan semua kelas
        return $query;
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->roles()->whereIn('name', ['admin', 'dosen'])->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelas::route('/'),
            'view' => Pages\ViewKelas::route('/{record}'),
            // 'create' => Pages\CreateKelas::route('/create'),
            // 'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }
}
