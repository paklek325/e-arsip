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

    protected static function booted()
    {
        static::updated(function ($kode) {
            // Jika kolom 'kode' berubah nilainya
            if ($kode->isDirty('kode')) {
                $originalKode = $kode->getOriginal('kode');
                $newKode = $kode->kode;
                
                // Update semua surat yang menggunakan kode lama menjadi kode baru
                // Kita gunakan method update pada query builder untuk update massal
                Surat::where('kode_surat', $originalKode)->update(['kode_surat' => $newKode]);
            }
        });
    }
}
