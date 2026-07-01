<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kode extends Model
{
    use HasFactory;

    protected $table = 'kode';

    protected $primaryKey = 'id_kode';

    /**
     * Tabel kode tidak pakai timestamps (created_at / updated_at).
     */
    public $timestamps = false;

    protected $fillable = [
        'kode',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi ke Surat (via kode_surat - teks)
    |--------------------------------------------------------------------------
    | Satu kode bisa dipakai oleh banyak surat (terutama Surat Keluar).
    */

    public function surat(): HasMany
    {
        return $this->hasMany(
            Surat::class,
            'kode_surat',
            'kode'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi ke Surat (via id_kode - FOREIGN KEY asli)
    |--------------------------------------------------------------------------
    */

    public function suratByFk(): HasMany
    {
        return $this->hasMany(
            Surat::class,
            'id_kode',
            'id_kode'
        );
    }
}
