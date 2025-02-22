<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat 10 kelas
        Kelas::factory(15)->create()->each(function ($kelas) {
            // Untuk setiap kelas, assign 5 mahasiswa
            KelasMahasiswa::factory(5)->create([
                'kelas_id' => $kelas->id,
            ]);
        });
    }
}
