<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KelasMahasiswa>
 */
class KelasMahasiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kelas_id' => Kelas::inRandomOrder()->first()->id ?? Kelas::factory()->create()->id,
            'mahasiswa_id' => User::role('mahasiswa')->inRandomOrder()->first()->id ?? User::factory()->create()->assignRole('mahasiswa')->id,
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected']),
        ];
    }
}
