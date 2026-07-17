<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PesertaDidik extends Model
{
    use HasFactory;

    protected $table = 'peserta_didik';
    protected $primaryKey = 'id_peserta_didik';

    // Mengaktifkan created_at & updated_at
    public $timestamps = true;

    protected $fillable = [
        'nama_peserta_didik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'rombel',
        'tahun_angkatan',
        'file_kk',
        'file_akte',
        'file_ktp',
        'file_ijazah_smp',
        'file_kip',
        'status',
        'id_user',
    ];

    /**
     * Auto set status sebelum save (create/update)
     */
    protected static function booted()
    {
        static::saving(function (PesertaDidik $pesertaDidik) {
            $pesertaDidik->status = $pesertaDidik->computeStatus();
        });
    }

    /**
     * Hitung status lengkap/belum lengkap berdasarkan file upload
     */
    public function computeStatus(): string
    {
        // file_kip tidak wajib (boleh dikosongi, status tetap lengkap)
        $files = [
            $this->file_kk,
            $this->file_akte,
            $this->file_ktp,
            $this->file_ijazah_smp,
        ];

        return collect($files)->every(fn($f) => !empty($f))
            ? 'lengkap'
            : 'belum lengkap';
    }

    /**
     * Helper
     */
    public function isComplete(): bool
    {
        return $this->status === 'lengkap';
    }

    /**
     * Scope query
     */
    public function scopeLengkap($query)
    {
        return $query->where('status', 'lengkap');
    }

    public function scopeBelumLengkap($query)
    {
        return $query->where('status', 'belum lengkap');
    }
    // Relasi: siswa diinput oleh user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
