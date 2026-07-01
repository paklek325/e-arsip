<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use stdClass; // Diperlukan untuk return type getMonthlyTotals

class Laporan extends Model
{
    use HasFactory;

    /**
     * Model ini berfungsi sebagai adapter tunggal untuk tabel 'surat'
     * yang mencakup Surat Masuk dan Surat Keluar (dibedakan oleh jenis_surat).
     */
    protected $table = 'surat';

    /**
     * Tentukan Primary Key tabel 'surat'.
     */
    protected $primaryKey = 'id';

    /**
     * Menggunakan timestamps (created_at / updated_at) pada tabel 'surat'.
     */
    public $timestamps = true;

    /**
     * Kolom yang dapat diisi secara massal (mass-assignable) pada tabel 'surat'.
     */
    protected $fillable = [
        'no_surat',
        'kode_surat',
        'jenis_surat', // Penting untuk rekap: nilai harus 'masuk' atau 'keluar'
        'perihal',
        'pengirim',
        'penerima',
        'tanggal_surat',
        'id_user',
        'created_at',
        // tambahkan kolom lain sesuai tabel Anda
    ];

    /* ============================
       SCOPES & HELPERS untuk rekap
       ============================ */

    /**
     * Scope untuk memfilter berdasarkan tahun (kolom tanggal_surat).
     */
    public function scopeForYear(Builder $q, int $year): Builder
    {
        return $q->whereYear('tanggal_surat', $year);
    }

    /**
     * Scope untuk memfilter berdasarkan bulan di tahun tertentu.
     */
    public function scopeForMonth(Builder $q, int $year, int $month): Builder
    {
        return $q->whereYear('tanggal_surat', $year)
            ->whereMonth('tanggal_surat', $month);
    }

    /**
     * Ambil agregat (jumlah masuk/keluar) per bulan untuk suatu tahun dari tabel 'surat'.
     * Return: collection keyed by bulan (1..12) dengan fields: bulan, total_masuk, total_keluar
     */
    public static function aggregatePerMonth(int $year)
    {
        $rows = DB::table('surat')
            ->selectRaw('MONTH(tanggal_surat) as bulan')
            // Hitung total_masuk dari jenis_surat = 'masuk'
            ->selectRaw('SUM(CASE WHEN jenis_surat = "Masuk" THEN 1 ELSE 0 END) as total_masuk')
            // Hitung total_keluar dari jenis_surat = 'Keluar'
            ->selectRaw('SUM(CASE WHEN jenis_surat = "Keluar" THEN 1 ELSE 0 END) as total_keluar')
            ->whereYear('tanggal_surat', $year)
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();

        // keyBy bulan agar mudah lookup
        return $rows->keyBy('bulan');
    }

    /**
     * BARU: Hitung total surat masuk dan keluar untuk bulan tertentu.
     * Digunakan oleh Controller untuk tabel Ringkasan Bulanan.
     * Return: stdClass (object dengan total_masuk dan total_keluar)
     */
    public static function getMonthlyTotals(int $year, int $month): stdClass
    {
        $result = DB::table('surat')
            ->selectRaw('
                SUM(CASE WHEN jenis_surat = "Masuk" THEN 1 ELSE 0 END) as total_masuk,
                SUM(CASE WHEN jenis_surat = "Keluar" THEN 1 ELSE 0 END) as total_keluar
            ')
            ->whereYear('tanggal_surat', $year)
            ->whereMonth('tanggal_surat', $month)
            ->first();

        // Pastikan mengembalikan objek standar meskipun hasilnya null
        return (object) [
            'total_masuk' => (int) ($result->total_masuk ?? 0),
            'total_keluar' => (int) ($result->total_keluar ?? 0),
        ];
    }

    /**
     * DIPERBARUI: Ambil daftar surat (masuk atau keluar) untuk bulan tertentu (dengan pagination dan filter jenis).
     */
    public static function getSuratForMonth(int $year, int $month, ?int $perPage = 10, ?string $jenis = null)
    {
        // 1. Inisialisasi query dasar untuk tahun dan bulan
        $query = self::forMonth($year, $month)->orderBy('tanggal_surat', 'desc');

        // 2. Terapkan filter jenis jika ada ('masuk' atau 'keluar')
        if ($jenis && in_array(strtolower($jenis), ['masuk', 'keluar'])) {
            $query->where('jenis_surat', ucfirst(strtolower($jenis))); // 'Masuk' atau 'Keluar'
        }

        // 3. Terapkan pagination
        if ($perPage) {
            // withQueryString() memastikan semua parameter filter (tipe, tahun, bulan, jenis) tetap ada
            return $query->paginate($perPage)->withQueryString();
        }

        return $query->get();
    }
}
