<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PesertaDidik;
use App\Models\Kode;
use App\Models\Surat;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Users ────────────────────────────────────────────────────
        $totalUser = User::count();

        $totalKepala = User::whereHas('role', function ($q) {
            $q->whereRaw('LOWER(name) like ?', ['%kepala%']);
        })->count();

        $totalStaf = User::whereHas('role', function ($q) {
            $q->whereRaw('LOWER(name) like ?', ['%staf%'])
                ->whereRaw('LOWER(name) not like ?', ['%kepala%']);
        })->count();

        // ── Surat ────────────────────────────────────────────────────
        // Ganti 'tanggal_surat' jika nama kolom tanggal surat di tabel berbeda
        $kolomTgl   = 'tanggal_surat';
        $tahunSurat = now()->year;

        $totalSurat       = Surat::count();
        $totalSuratMasuk  = Surat::where('jenis_surat', 'Masuk')->whereYear($kolomTgl, $tahunSurat)->count();
        $totalSuratKeluar = Surat::where('jenis_surat', 'Keluar')->whereYear($kolomTgl, $tahunSurat)->count();

        // ── Tren Surat Bulanan (tahun ini) — pakai tanggal surat ─────
        $trenSuratMasuk  = array_fill(0, 12, 0);
        $trenSuratKeluar = array_fill(0, 12, 0);

        Surat::whereYear($kolomTgl, $tahunSurat)
            ->get(['jenis_surat', $kolomTgl])
            ->each(function ($surat) use (&$trenSuratMasuk, &$trenSuratKeluar, $kolomTgl) {
                $bulanIndex = (int) \Carbon\Carbon::parse($surat->$kolomTgl)->format('n') - 1;
                if ($surat->jenis_surat === 'Masuk') {
                    $trenSuratMasuk[$bulanIndex]++;
                } else {
                    $trenSuratKeluar[$bulanIndex]++;
                }
            });

        // ── Tren Surat Bulanan (semua tahun) — pakai tanggal surat ───
        $trenSuratMasukAll  = array_fill(0, 12, 0);
        $trenSuratKeluarAll = array_fill(0, 12, 0);

        Surat::whereNotNull($kolomTgl)
            ->get(['jenis_surat', $kolomTgl])
            ->each(function ($surat) use (&$trenSuratMasukAll, &$trenSuratKeluarAll, $kolomTgl) {
                $bulanIndex = (int) \Carbon\Carbon::parse($surat->$kolomTgl)->format('n') - 1;
                if ($surat->jenis_surat === 'Masuk') {
                    $trenSuratMasukAll[$bulanIndex]++;
                } else {
                    $trenSuratKeluarAll[$bulanIndex]++;
                }
            });

        // ── Total Surat semua tahun (Masuk & Keluar) ─────────────────
        $totalSuratMasukAll  = Surat::where('jenis_surat', 'Masuk')->count();
        $totalSuratKeluarAll = Surat::where('jenis_surat', 'Keluar')->count();

        // ── Surat dikelompokkan per tahun (untuk spinner filter tahun) ─
        $trenSuratPerTahun = [];

        Surat::whereNotNull($kolomTgl)
            ->get(['jenis_surat', $kolomTgl])
            ->each(function ($surat) use (&$trenSuratPerTahun, $kolomTgl) {
                $tahun = (int) \Carbon\Carbon::parse($surat->$kolomTgl)->format('Y');
                if (!isset($trenSuratPerTahun[$tahun])) {
                    $trenSuratPerTahun[$tahun] = ['masuk' => 0, 'keluar' => 0];
                }
                if ($surat->jenis_surat === 'Masuk') {
                    $trenSuratPerTahun[$tahun]['masuk']++;
                } else {
                    $trenSuratPerTahun[$tahun]['keluar']++;
                }
            });

        ksort($trenSuratPerTahun);

        // ── PesertaDidik ────────────────────────────────────────────────────
        // Cari angkatan yang mengandung tahun sekarang (misal "2026" atau "2026-2027")
        // Jika tidak ada, fallback ke angkatan terbaru yang ada di DB.
        $tahunNow   = now()->year;
        $tahunAktif = PesertaDidik::where('tahun_angkatan', 'LIKE', "%{$tahunNow}%")
            ->orderByRaw("CAST(SUBSTRING_INDEX(tahun_angkatan, '-', 1) AS UNSIGNED) DESC")
            ->value('tahun_angkatan');
        $tahunAktif = $tahunAktif ?? PesertaDidik::orderByRaw("CAST(SUBSTRING_INDEX(tahun_angkatan, '-', 1) AS UNSIGNED) DESC")
            ->value('tahun_angkatan') ?? $tahunNow;

        $totalPesertaDidik  = PesertaDidik::where('tahun_angkatan', $tahunAktif)->count();

        $peserta_didikPerRombel = PesertaDidik::selectRaw('rombel, count(*) as total')
            ->where('tahun_angkatan', $tahunAktif)
            ->whereNotNull('rombel')
            ->where('rombel', '!=', '')
            ->groupBy('rombel')
            ->orderBy('rombel')
            ->pluck('total', 'rombel');

        // Jenis kelamin — tahun aktif
        $peserta_didikLaki      = PesertaDidik::where('tahun_angkatan', $tahunAktif)->where('jenis_kelamin', 'L')->count();
        $peserta_didikPerempuan = PesertaDidik::where('tahun_angkatan', $tahunAktif)->where('jenis_kelamin', 'P')->count();

        // ── PesertaDidik semua tahun ─────────────────────────────────────────
        $totalPesertaDidikAll = PesertaDidik::count();

        $peserta_didikPerRombelAll = PesertaDidik::selectRaw('rombel, count(*) as total')
            ->whereNotNull('rombel')
            ->where('rombel', '!=', '')
            ->groupBy('rombel')
            ->orderBy('rombel')
            ->pluck('total', 'rombel');

        // Rincian L/P per rombel (semua tahun) — dipakai di keterangan/legend donut chart
        $peserta_didikPerRombelGenderAll = [];

        PesertaDidik::whereNotNull('rombel')
            ->where('rombel', '!=', '')
            ->get(['rombel', 'jenis_kelamin'])
            ->each(function ($peserta_didik) use (&$peserta_didikPerRombelGenderAll) {
                $rombel = $peserta_didik->rombel;
                if (!isset($peserta_didikPerRombelGenderAll[$rombel])) {
                    $peserta_didikPerRombelGenderAll[$rombel] = ['laki' => 0, 'perempuan' => 0, 'total' => 0];
                }
                if ($peserta_didik->jenis_kelamin === 'L') {
                    $peserta_didikPerRombelGenderAll[$rombel]['laki']++;
                } elseif ($peserta_didik->jenis_kelamin === 'P') {
                    $peserta_didikPerRombelGenderAll[$rombel]['perempuan']++;
                }
                $peserta_didikPerRombelGenderAll[$rombel]['total']++;
            });

        ksort($peserta_didikPerRombelGenderAll);

        // Jenis kelamin — semua tahun
        $peserta_didikLakiAll      = PesertaDidik::where('jenis_kelamin', 'L')->count();
        $peserta_didikPerempuanAll = PesertaDidik::where('jenis_kelamin', 'P')->count();

        // ── PesertaDidik dikelompokkan per tahun angkatan (untuk filter tahun manual di chart) ──
        // tahun_angkatan bisa berformat "2026" atau "2026-2027", ambil tahun awalnya sebagai key.
        $peserta_didikPerTahun = [];

        PesertaDidik::whereNotNull('tahun_angkatan')
            ->where('tahun_angkatan', '!=', '')
            ->get(['tahun_angkatan', 'rombel', 'jenis_kelamin'])
            ->each(function ($peserta_didik) use (&$peserta_didikPerTahun) {
                $tahunAwal = (int) \Illuminate\Support\Str::before($peserta_didik->tahun_angkatan, '-');

                if (!isset($peserta_didikPerTahun[$tahunAwal])) {
                    $peserta_didikPerTahun[$tahunAwal] = [
                        'rombel'    => [],
                        'laki'      => 0,
                        'perempuan' => 0,
                        'total'     => 0,
                    ];
                }

                $rombel = $peserta_didik->rombel ?: 'Tidak Diketahui';
                $peserta_didikPerTahun[$tahunAwal]['rombel'][$rombel] = ($peserta_didikPerTahun[$tahunAwal]['rombel'][$rombel] ?? 0) + 1;

                if (!isset($peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel])) {
                    $peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel] = ['laki' => 0, 'perempuan' => 0, 'total' => 0];
                }
                $peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel]['total']++;
                if ($peserta_didik->jenis_kelamin === 'L') {
                    $peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel]['laki']++;
                } elseif ($peserta_didik->jenis_kelamin === 'P') {
                    $peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel]['perempuan']++;
                }

                if ($peserta_didik->jenis_kelamin === 'L') {
                    $peserta_didikPerTahun[$tahunAwal]['laki']++;
                } elseif ($peserta_didik->jenis_kelamin === 'P') {
                    $peserta_didikPerTahun[$tahunAwal]['perempuan']++;
                }

                $peserta_didikPerTahun[$tahunAwal]['total']++;
            });

        // Urutkan rombel di setiap tahun agar urutan badge & warna chart konsisten (A sebelum B)
        foreach ($peserta_didikPerTahun as &$tData) {
            ksort($tData['rombel']);
            if (isset($tData['rombelGender'])) {
                ksort($tData['rombelGender']);
            }
        }
        unset($tData);

        ksort($peserta_didikPerTahun);

        // Tahun-tahun yang tersedia datanya (untuk batas min/max input spinner)
        $tahunPesertaDidikMin = !empty($peserta_didikPerTahun) ? min(array_keys($peserta_didikPerTahun)) : now()->year;
        $tahunPesertaDidikMax = !empty($peserta_didikPerTahun) ? max(array_keys($peserta_didikPerTahun)) : now()->year;

        // ── Kode & Recent Users ──────────────────────────────────────
        $totalKode   = Kode::count();
        $recentUsers = User::with('role')->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalUser',
            'totalKepala',
            'totalStaf',
            'totalSurat',
            'totalSuratMasuk',
            'totalSuratKeluar',
            'trenSuratMasuk',
            'trenSuratKeluar',
            'trenSuratMasukAll',
            'trenSuratKeluarAll',
            'totalSuratMasukAll',
            'totalSuratKeluarAll',
            'trenSuratPerTahun',
            'totalPesertaDidik',
            'peserta_didikPerRombel',
            'tahunAktif',
            'peserta_didikLaki',
            'peserta_didikPerempuan',
            'totalPesertaDidikAll',
            'peserta_didikPerRombelAll',
            'peserta_didikPerRombelGenderAll',
            'peserta_didikLakiAll',
            'peserta_didikPerempuanAll',
            'peserta_didikPerTahun',
            'tahunPesertaDidikMin',
            'tahunPesertaDidikMax',
            'totalKode',
            'recentUsers'
        ));
    }
}
