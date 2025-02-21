<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class, // Seeder role admin, dosen, mahasiswa
            UserSeeder::class, // Seeder untuk user
            KelasSeeder::class, // Seeder untuk kelas dan kelas_mahasiswa
        ]);
    }
}
