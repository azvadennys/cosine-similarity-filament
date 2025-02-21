<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat 1 admin
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Buat 1 Dosen
        User::factory()->dosen()->create([
            'name' => 'Dosen User',
            'email' => 'dosen@example.com',
        ]);

        // Buat 1 Mahasiswa
        User::factory()->mahasiswa()->create([
            'name' => 'Mahasiswa User',
            'email' => 'mahasiswa@example.com',
        ]);

        // Buat 5 dosen
        User::factory(5)->dosen()->create();

        // Buat 10 mahasiswa
        User::factory(15)->mahasiswa()->create();
    }
}
