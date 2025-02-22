<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages;
use App\Filament\Resources\KelasResource\RelationManagers;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as ActionsAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

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
                TextInput::make('kode_bergabung')
                    ->label('Kode Bergabung')
                    ->required()
                    ->maxLength(4)
                    ->unique(ignoreRecord: true)
                    ->suffixAction(
                        ActionsAction::make('generate_code')
                            ->icon('heroicon-m-arrow-path')
                            ->tooltip('Generate Kode')
                            ->action(function ($state, $set) {
                                $generatedCode = strtoupper(Str::random(4)); // Generate kode 4 karakter
                                $set('kode_bergabung', $generatedCode); // Set ke input field
                            })
                    ),
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
                Tables\Columns\TextColumn::make('kode_bergabung')
                    ->label('Kode Bergabung')
                    ->visible(fn() => auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('dosen'))
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('request_join')
                    ->label(function ($record) {
                        $isJoined = KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
                            ->exists();

                        return $isJoined ? 'Joined' : 'Request Join';
                    })
                    ->color(function ($record) {
                        $isJoined = KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
                            ->exists();

                        return $isJoined ? 'success' : 'primary';
                    })
                    ->icon(function ($record) {
                        $isJoined = KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
                            ->exists();

                        return $isJoined ? 'heroicon-o-check-circle' : 'heroicon-o-paper-airplane';
                    })
                    ->button()
                    ->visible(fn() => auth()->user()?->hasRole('mahasiswa'))
                    ->disabled(function ($record) {
                        return KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
                            ->exists();
                    })
                    ->form([
                        TextInput::make('kode_bergabung')
                            ->label('Masukkan Kode Kelas')
                            ->required()
                            ->maxLength(4),
                    ])
                    ->action(function ($record, array $data, $action) {
                        $inputKodeKelas = $data['kode_bergabung'];

                        // Cek apakah kode kelas sesuai
                        if ($record->kode_bergabung === $inputKodeKelas) {
                            // Buat record di KelasMahasiswa
                            KelasMahasiswa::create([
                                'kelas_id' => $record->id,
                                'mahasiswa_id' => auth()->id(),
                            ]);

                            // Notifikasi berhasil
                            Notification::make()
                                ->title('Berhasil Bergabung')
                                ->body("Anda berhasil bergabung ke kelas {$record->nama_kelas}.")
                                ->success()
                                ->send();
                        } else {
                            // Notifikasi berhasil
                            Notification::make()
                                ->title('Gagal Bergabung')
                                ->body("Kode yang anda masukkan salah")
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation(),

                Tables\Actions\ViewAction::make()
                    ->visible(
                        fn($record) =>
                        auth()->user()?->hasRole('admin') || // Admin bisa melihat semua
                            $record->dosen_id === auth()->id() || // Dosen hanya bisa melihat kelasnya sendiri
                            KelasMahasiswa::where('kelas_id', $record->id)
                            ->where('mahasiswa_id', auth()->id())
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
