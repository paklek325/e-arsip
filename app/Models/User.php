<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user'; // PK bukan 'id'
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_role',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /********************
     * 🔗 RELASI
     ********************/
    public function role()
    {
        // pastikan foreign key dan local key sesuai
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    /**
     * Relasi ke surat-surat yang diinput oleh user ini.
     */
    public function surat()
    {
        return $this->hasMany(Surat::class, 'id_user', 'id_user');
    }

    /**
     * Relasi ke data peserta didik yang diinput oleh user ini.
     */
    public function peserta_didik()
    {
        return $this->hasMany(PesertaDidik::class, 'id_user', 'id_user');
    }

    /********************
     * 🔒 MUTATOR PASSWORD
     ********************/
    public function setPasswordAttribute($value)
    {
        // Jika kosong, jangan ubah apapun (agar tidak override password lama)
        if (empty($value)) return;

        // Cek apakah sudah di-hash (Hash::info hanya bisa mengenali bcrypt)
        $info = Hash::info($value);

        // Kalau belum hash (algo = null), baru di-hash
        if (!$info['algo']) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }
}
