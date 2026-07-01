<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanRekap extends Model
{
    use HasFactory;

    protected $table = 'laporan';
    
    protected $primaryKey = 'id_rekap';

    public $timestamps = false; // Karena tabel hanya punya created_at, kita matikan timestamps eloquent (atau override UPDATED_AT = null)
    
    const UPDATED_AT = null;

    protected $fillable = [
        'tahun',
        'bulan',
        'tipe_rekap',
        'total_surat_masuk',
        'total_surat_keluar',
        'total_jenis',
        'dibuat_oleh',
        'created_at'
    ];

    /**
     * Relasi One-to-Many ke tabel surat.
     * Satu laporan rekap (misalnya laporan bulanan) bisa memayungi banyak surat.
     */
    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'id_rekap', 'id_rekap');
    }
}
