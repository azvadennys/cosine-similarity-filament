<?php

namespace App\Filament\Resources\KelasResource\Pages;

use App\Filament\Resources\KelasResource;
use App\Models\Kelas;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use App\Models\KelasMahasiswa;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class ViewKelas extends ViewRecord implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    protected static string $view = 'filament.pages.view-kelas';

    protected static string $resource = KelasResource::class;

    public function getHeading(): string
    {
        return $this->record->nama_kelas;
    }

    public function getSubheading(): ?string
    {
        return "Dosen: " . $this->record->dosen->name;
    }

    /**
     * Query untuk mengambil daftar mahasiswa berdasarkan role pengguna.
     */
    protected function getTableQuery()
    {
        $query = KelasMahasiswa::with('mahasiswa') // Pastikan relasi 'mahasiswa' ada di model
            ->where('kelas_id', $this->record->id);

        // Mahasiswa hanya bisa melihat mahasiswa yang accepted
        if (auth()->user()?->hasRole('mahasiswa')) {
            $query->where('status', 'accepted');
        }

        return $query;
    }


    /**
     * Konfigurasi kolom-kolom untuk tabel daftar mahasiswa.
     */
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('mahasiswa.name') // Relasi ke model User
                ->label('Nama Mahasiswa')
                ->searchable(),

            TextColumn::make('mahasiswa.email') // Email Mahasiswa
                ->label('Email')
                ->searchable(),

            BadgeColumn::make('status') // Status dari tabel pivot
                ->label('Status')
                ->colors([
                    'warning' => 'pending',
                    'success' => 'accepted',
                    'danger' => 'rejected',
                ])
                ->formatStateUsing(fn($state) => ucfirst($state)),
        ];
    }

    /**
     * Tidak ada aksi tambahan di header.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
