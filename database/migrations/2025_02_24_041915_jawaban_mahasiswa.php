<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migration untuk tabel jawaban_mahasiswa
        Schema::create('jawaban_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_tugas_id')->constrained('soal_tugas')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade'); // Relasi ke mahasiswa
            $table->string('jawaban_mahasiswa'); // Jawaban yang dipilih
            $table->text('alasan'); // Alasan memilih jawaban
            $table->decimal('nilai', 5, 2)->nullable(); // Nilai dari jawaban
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_mahasiswa');
    }
};
