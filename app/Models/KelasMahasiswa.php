<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasMahasiswa extends Model
{
    use HasFactory;

    protected $table = "kelas_mahasiswa";
    protected $fillable = ['kelas_id', 'mahasiswa_id', 'status'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function all_mahasiswa()
    {
        return $this->hasMany(User::class, 'mahasiswa_id');
    }
}
