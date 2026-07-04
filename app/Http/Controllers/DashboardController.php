<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PesertaDidik;
use App\Models\Kode;
use App\Models\Surat;
use Illuminate\Support\Str;

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
        $kolomTgl   = 'tanggal_surat';
        $tahunSurat = now()->year;

        $totalSurat = Surat::count();

        // Count masuk & keluar tahun ini — 1 query
        $suratCountTahunIni = Surat::whereYear($kolomTgl, $tahunSurat)
            ->selectRaw("
                SUM(CASE WHEN jenis_surat = 'Masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN jenis_surat = 'Keluar' THEN 1 ELSE 0 END) as keluar
            ")->first();
        $totalSuratMasuk  = $suratCountTahunIni->masuk  ?? 0;
        $totalSuratKeluar = $suratCountTahunIni->keluar ?? 0;

        // Tren bulanan tahun ini — 1 GROUP BY query, bukan get()->each()
        $trenSuratMasuk  = array_fill(0, 12, 0);
        $trenSuratKeluar = array_fill(0, 12, 0);

        Surat::whereYear($kolomTgl, $tahunSurat)
            ->selectRaw("MONTH($kolomTgl) as bulan, jenis_surat, COUNT(*) as cnt")
            ->groupBy('bulan', 'jenis_surat')
            ->get()
            ->each(function ($row) use (&$trenSuratMasuk, &$trenSuratKeluar) {
                $idx = $row->bulan - 1;
                if ($row->jenis_surat === 'Masuk') $trenSuratMasuk[$idx]  = $row->cnt;
                else                               $trenSuratKeluar[$idx] = $row->cnt;
            });

        // Tren bulanan semua tahun — 1 GROUP BY query
        $trenSuratMasukAll  = array_fill(0, 12, 0);
        $trenSuratKeluarAll = array_fill(0, 12, 0);

        Surat::whereNotNull($kolomTgl)
            ->selectRaw("MONTH($kolomTgl) as bulan, jenis_surat, COUNT(*) as cnt")
            ->groupBy('bulan', 'jenis_surat')
            ->get()
            ->each(function ($row) use (&$trenSuratMasukAll, &$trenSuratKeluarAll) {
                $idx = $row->bulan - 1;
                if ($row->jenis_surat === 'Masuk') $trenSuratMasukAll[$idx]  = $row->cnt;
                else                               $trenSuratKeluarAll[$idx] = $row->cnt;
            });

        // Count all-time masuk & keluar — 1 query
        $suratCountAll = Surat::selectRaw("
            SUM(CASE WHEN jenis_surat = 'Masuk' THEN 1 ELSE 0 END) as masuk,
            SUM(CASE WHEN jenis_surat = 'Keluar' THEN 1 ELSE 0 END) as keluar
        ")->first();
        $totalSuratMasukAll  = $suratCountAll->masuk  ?? 0;
        $totalSuratKeluarAll = $suratCountAll->keluar ?? 0;

        // Surat per tahun — 1 GROUP BY query
        $trenSuratPerTahun = [];

        Surat::whereNotNull($kolomTgl)
            ->selectRaw("YEAR($kolomTgl) as tahun, jenis_surat, COUNT(*) as cnt")
            ->groupBy('tahun', 'jenis_surat')
            ->get()
            ->each(function ($row) use (&$trenSuratPerTahun) {
                $tahun = $row->tahun;
                if (!isset($trenSuratPerTahun[$tahun])) {
                    $trenSuratPerTahun[$tahun] = ['masuk' => 0, 'keluar' => 0];
                }
                if ($row->jenis_surat === 'Masuk') $trenSuratPerTahun[$tahun]['masuk']  = $row->cnt;
                else                               $trenSuratPerTahun[$tahun]['keluar'] = $row->cnt;
            });

        ksort($trenSuratPerTahun);

        // ── PesertaDidik ─────────────────────────────────────────────
        $tahunNow   = now()->year;
        $tahunAktif = PesertaDidik::where('tahun_angkatan', 'LIKE', "%{$tahunNow}%")
            ->orderByRaw("CAST(SUBSTRING_INDEX(tahun_angkatan, '-', 1) AS UNSIGNED) DESC")
            ->value('tahun_angkatan');
        $tahunAktif = $tahunAktif ?? PesertaDidik::orderByRaw("CAST(SUBSTRING_INDEX(tahun_angkatan, '-', 1) AS UNSIGNED) DESC")
            ->value('tahun_angkatan') ?? $tahunNow;

        $totalPesertaDidik = PesertaDidik::where('tahun_angkatan', $tahunAktif)->count();

        $peserta_didikPerRombel = PesertaDidik::selectRaw('rombel, count(*) as total')
            ->where('tahun_angkatan', $tahunAktif)
            ->whereNotNull('rombel')
            ->where('rombel', '!=', '')
            ->groupBy('rombel')
            ->orderBy('rombel')
            ->pluck('total', 'rombel');

        // Gender tahun aktif — 1 query
        $pdGenderAktif = PesertaDidik::where('tahun_angkatan', $tahunAktif)
            ->selectRaw("
                SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as laki,
                SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as perempuan
            ")->first();
        $peserta_didikLaki      = $pdGenderAktif->laki      ?? 0;
        $peserta_didikPerempuan = $pdGenderAktif->perempuan ?? 0;

        $totalPesertaDidikAll = PesertaDidik::count();

        $peserta_didikPerRombelAll = PesertaDidik::selectRaw('rombel, count(*) as total')
            ->whereNotNull('rombel')
            ->where('rombel', '!=', '')
            ->groupBy('rombel')
            ->orderBy('rombel')
            ->pluck('total', 'rombel');

        // Gender per rombel semua tahun — 1 GROUP BY query, bukan get()->each()
        $peserta_didikPerRombelGenderAll = [];

        PesertaDidik::whereNotNull('rombel')
            ->where('rombel', '!=', '')
            ->selectRaw('rombel, jenis_kelamin, COUNT(*) as cnt')
            ->groupBy('rombel', 'jenis_kelamin')
            ->get()
            ->each(function ($row) use (&$peserta_didikPerRombelGenderAll) {
                $rombel = $row->rombel;
                if (!isset($peserta_didikPerRombelGenderAll[$rombel])) {
                    $peserta_didikPerRombelGenderAll[$rombel] = ['laki' => 0, 'perempuan' => 0, 'total' => 0];
                }
                if ($row->jenis_kelamin === 'L')      $peserta_didikPerRombelGenderAll[$rombel]['laki']      = $row->cnt;
                elseif ($row->jenis_kelamin === 'P')  $peserta_didikPerRombelGenderAll[$rombel]['perempuan'] = $row->cnt;
                $peserta_didikPerRombelGenderAll[$rombel]['total'] += $row->cnt;
            });

        ksort($peserta_didikPerRombelGenderAll);

        // Gender semua tahun — 1 query
        $pdGenderAll = PesertaDidik::selectRaw("
            SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as laki,
            SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as perempuan
        ")->first();
        $peserta_didikLakiAll      = $pdGenderAll->laki      ?? 0;
        $peserta_didikPerempuanAll = $pdGenderAll->perempuan ?? 0;

        // PesertaDidik per tahun angkatan — 1 GROUP BY query, bukan get()->each()
        $peserta_didikPerTahun = [];

        PesertaDidik::whereNotNull('tahun_angkatan')
            ->where('tahun_angkatan', '!=', '')
            ->selectRaw('tahun_angkatan, rombel, jenis_kelamin, COUNT(*) as cnt')
            ->groupBy('tahun_angkatan', 'rombel', 'jenis_kelamin')
            ->get()
            ->each(function ($row) use (&$peserta_didikPerTahun) {
                $tahunAwal = (int) Str::before($row->tahun_angkatan, '-');
                $rombel    = $row->rombel ?: 'Tidak Diketahui';

                if (!isset($peserta_didikPerTahun[$tahunAwal])) {
                    $peserta_didikPerTahun[$tahunAwal] = [
                        'rombel'      => [],
                        'rombelGender' => [],
                        'laki'        => 0,
                        'perempuan'   => 0,
                        'total'       => 0,
                    ];
                }

                $peserta_didikPerTahun[$tahunAwal]['rombel'][$rombel]
                    = ($peserta_didikPerTahun[$tahunAwal]['rombel'][$rombel] ?? 0) + $row->cnt;

                if (!isset($peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel])) {
                    $peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel] = ['laki' => 0, 'perempuan' => 0, 'total' => 0];
                }
                $peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel]['total'] += $row->cnt;

                if ($row->jenis_kelamin === 'L') {
                    $peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel]['laki'] += $row->cnt;
                    $peserta_didikPerTahun[$tahunAwal]['laki']                          += $row->cnt;
                } elseif ($row->jenis_kelamin === 'P') {
                    $peserta_didikPerTahun[$tahunAwal]['rombelGender'][$rombel]['perempuan'] += $row->cnt;
                    $peserta_didikPerTahun[$tahunAwal]['perempuan']                          += $row->cnt;
                }

                $peserta_didikPerTahun[$tahunAwal]['total'] += $row->cnt;
            });

        foreach ($peserta_didikPerTahun as &$tData) {
            ksort($tData['rombel']);
            if (isset($tData['rombelGender'])) ksort($tData['rombelGender']);
        }
        unset($tData);

        ksort($peserta_didikPerTahun);

        $tahunPesertaDidikMin = !empty($peserta_didikPerTahun) ? min(array_keys($peserta_didikPerTahun)) : now()->year;
        $tahunPesertaDidikMax = !empty($peserta_didikPerTahun) ? max(array_keys($peserta_didikPerTahun)) : now()->year;

        // ── Kode & Recent Users ──────────────────────────────────────
        $totalKode    = Kode::count();
        $kepalaRoleId = \App\Models\Role::where('name', 'Kepala Staf')->value('id_role');
        $recentUsers  = User::with('role')
            ->orderByRaw('CASE WHEN id_role = ? THEN 0 ELSE 1 END', [$kepalaRoleId])
            ->orderBy('name', 'asc')
            ->get();

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
