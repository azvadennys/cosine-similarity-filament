<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kelas>
 */
class KelasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kelas' => $this->faker->unique()->sentence(3),
            'deskripsi' => $this->faker->paragraph,
            'dosen_id' => User::role('dosen')->inRandomOrder()->first()->id ?? User::factory()->create()->assignRole('dosen')->id,
            'kode_bergabung' => strtoupper("AAAA")
        ];
    }
}
