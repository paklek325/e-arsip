<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Surat extends Model
{
    use HasFactory;

    protected $table = 'surat';

    protected $primaryKey = 'id';

    protected $fillable = [

        'no_surat',

        'kode_surat',

        'id_kode',

        'jenis_surat',

        'tanggal_surat',

        'perihal',

        'instansi',

        'pengirim',

        'penerima',

        'keterangan',

        'file_surat',

        /*
        |--------------------------------------------------------------------------
        | Informasi Arsip
        |--------------------------------------------------------------------------
        */

        'id_user',

        'updated_by',

    ];

    protected $casts = [

        'tanggal_surat' => 'date',

        'file_surat' => 'array',

        'created_at' => 'datetime',

        'updated_at' => 'datetime',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi Master Kode (via kode_surat - teks, untuk tampilan & pencarian)
    |--------------------------------------------------------------------------
    */

    public function kode(): BelongsTo
    {
        return $this->belongsTo(

            Kode::class,

            'kode_surat',

            'kode'

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Master Kode (via id_kode - FOREIGN KEY asli)
    |--------------------------------------------------------------------------
    | Ini relasi yang benar-benar ditegakkan di level database (terlihat
    | sebagai garis relasi di diagram ERD). kode_surat (teks) tetap
    | dipakai untuk tampilan/pencarian & mendukung kode bebas Surat Masuk,
    | sedangkan id_kode adalah rujukan resmi ke baris master kode.
    |--------------------------------------------------------------------------
    */

    public function kodeMaster(): BelongsTo
    {
        return $this->belongsTo(

            Kode::class,

            'id_kode',

            'id_kode'

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pembuat Surat
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(

            User::class,

            'id_user',

            'id_user'

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Editor Terakhir
    |--------------------------------------------------------------------------
    */

    public function editor(): BelongsTo
    {
        return $this->belongsTo(

            User::class,

            'updated_by',

            'id_user'

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getStatusEditAttribute(): string
    {
        if (
            !$this->created_at ||
            !$this->updated_at
        ) {

            return 'Belum Pernah Diubah';
        }

        return $this->created_at->equalTo($this->updated_at)

            ? 'Belum Pernah Diubah'

            : 'Pernah Diubah';
    }

    public function getNamaPembuatAttribute(): string
    {
        return optional(
            $this->user
        )->name ?? '-';
    }

    public function getNamaEditorAttribute(): string
    {
        return optional(
            $this->editor
        )->name ?? '-';
    }
}
