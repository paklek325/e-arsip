<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\PesertaDidik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    /* =====================================================================
     | Halaman Pilihan Backup
     | GET /backup
     ===================================================================== */
    public function index()
    {
        // PENTING: pakai withoutGlobalScopes() di halaman backup. Kalau model
        // Surat/PesertaDidik punya global scope (mis. hanya menampilkan data
        // milik user login / wali kelas tertentu), maka TANPA baris ini
        // filter tahun, daftar rombel, dan semua angka statistik di halaman
        // ini akan diam-diam ikut ke-filter juga — inilah biasanya penyebab
        // "kok cuma rombel B yang muncul" padahal di database ada rombel lain.
        $tahunSurat = Surat::withoutGlobalScopes()
            ->selectRaw('YEAR(tanggal_surat) as tahun')
            ->whereNotNull('tanggal_surat')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $tahunPeserta = PesertaDidik::withoutGlobalScopes()
            ->selectRaw('tahun_angkatan')
            ->distinct()
            ->orderByDesc('tahun_angkatan')
            ->pluck('tahun_angkatan');

        $rombelList = PesertaDidik::withoutGlobalScopes()
            ->selectRaw('rombel')
            ->whereNotNull('rombel')
            ->where('rombel', '!=', '')
            ->distinct()
            ->orderBy('rombel')
            ->pluck('rombel');

        $countSuratMasuk  = Surat::withoutGlobalScopes()->where('jenis_surat', 'Masuk')->count();
        $countSuratKeluar = Surat::withoutGlobalScopes()->where('jenis_surat', 'Keluar')->count();
        $countPeserta     = PesertaDidik::withoutGlobalScopes()->count();

        $statSurat   = $this->countFileSurat();
        $statPeserta = $this->countFilePeserta();

        return view('backup.index', compact(
            'tahunSurat',
            'tahunPeserta',
            'rombelList',
            'countSuratMasuk',
            'countSuratKeluar',
            'countPeserta',
            'statSurat',
            'statPeserta'
        ));
    }

    /* =====================================================================
     | Generate & Download Backup ZIP
     | POST /backup/generate
     ===================================================================== */
    public function generate(Request $request)
    {
        $request->validate([
            'tipe'           => 'required|in:surat,peserta_didik,semua',
            'tahun_surat'    => 'nullable',
            'jenis_surat'    => 'nullable|in:Masuk,Keluar,semua',
            'tahun_peserta'  => 'nullable',
            'rombel'         => 'nullable',
        ]);

        // Beri ruang & waktu ekstra — backup bisa berisi banyak file besar.
        // Kalau ini timeout/OOM di tengah zip->close(), hasilnya ZIP setengah
        // jadi alias "rusak/corrupt" ketika dibuka.
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $tipe    = $request->tipe;
        $zipName = 'backup_earsip_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/temp_backup/' . $zipName);

        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }
        // Bersihkan sisa file temp lama kalau ada, supaya tidak mewarisi
        // ZIP korup dari percobaan sebelumnya.
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat file ZIP. Periksa permission folder storage.',
            ], 500);
        }

        $totalAdded    = 0;
        $missingReport = [];

        $tahunSuratArray   = $this->normalizeFilterArray($request->tahun_surat);
        $tahunPesertaArray = $this->normalizeFilterArray($request->tahun_peserta);
        $rombelArray       = $this->normalizeFilterArray($request->rombel);

        // ── Backup Surat ────────────────────────────────────────────
        if (in_array($tipe, ['surat', 'semua'])) {
            $result      = $this->addSuratToZip($zip, $tahunSuratArray, $request->jenis_surat ?? 'semua');
            $totalAdded += $result['added'];
            $missingReport = array_merge($missingReport, $result['missing']);
        }

        // ── Backup Peserta Didik ─────────────────────────────────────
        if (in_array($tipe, ['peserta_didik', 'semua'])) {
            $result      = $this->addPesertaToZip($zip, $tahunPesertaArray, $rombelArray);
            $totalAdded += $result['added'];
            $missingReport = array_merge($missingReport, $result['missing']);
        }

        // Sertakan laporan file yang tidak ditemukan di disk, supaya admin
        // tahu persis file mana yang bermasalah — bukan menebak-nebak lagi
        // kenapa arsip "kosong/rusak".
        if (!empty($missingReport)) {
            $zip->addFromString(
                '_LAPORAN_FILE_TIDAK_DITEMUKAN.txt',
                "=== CATATAN STATUS FILE BACKUP E-ARSIP ===\n"
                . "Dibuat: " . now()->format('Y-m-d H:i:s') . "\n\n"
                . "Informasi berikut mencatat data/siswa yang terdaftar di database tetapi belum/tidak memiliki fisik berkas dokumen terlampir di folder storage:\n\n"
                . implode("\n", $missingReport) . "\n\n"
                . "Catatan: Untuk siswa (seperti Rany & Cika pada Rombel A), data siswa tetap terdaftar di database, namun berkas (seperti KK/Akte/KTP) memang belum pernah diunggah."
            );
        }

        $closeOk = $zip->close();

        // ── Validasi integritas ZIP sebelum dikirim ───────────────────
        // Ini bagian penting yang sebelumnya tidak ada: kalau close() gagal
        // atau ukuran file tidak masuk akal (ZIP kosong minimal 22 byte
        // untuk End-Of-Central-Directory record), JANGAN kirim ke user
        // sebagai file yang "berhasil" — itu sumber laporan "rusak lagi".
        if (!$closeOk || !file_exists($zipPath) || filesize($zipPath) < 22) {
            @unlink($zipPath);
            Log::error("Backup ZIP gagal ditutup dengan benar atau file tidak valid: {$zipName}");
            return response()->json([
                'success' => false,
                'message' => 'Proses pembuatan ZIP gagal di tahap akhir (file tidak valid). Coba lagi atau kurangi cakupan filter.',
            ], 500);
        }

        // Double-check ZIP benar-benar bisa dibuka lagi (deteksi dini
        // corrupt) sebelum kita commit untuk mengirimkannya ke user.
        $verify = new ZipArchive();
        if ($verify->open($zipPath, ZipArchive::CHECKCONS) !== true) {
            @unlink($zipPath);
            Log::error("Backup ZIP gagal lolos verifikasi konsistensi: {$zipName}");
            return response()->json([
                'success' => false,
                'message' => 'File ZIP yang dihasilkan tidak konsisten. Silakan coba generate ulang.',
            ], 500);
        }
        $verify->close();

        if ($totalAdded === 0) {
            @unlink($zipPath);
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada file yang ditemukan sesuai filter yang dipilih.',
            ], 404);
        }

        Log::info("Backup ZIP dibuat: {$zipName} ({$totalAdded} file, " . count($missingReport) . " hilang)");

        // Matikan output buffering/kompresi sebelum stream binary — pada
        // sebagian hosting, zlib.output_compression atau ob_gzhandler yang
        // aktif bisa "menyisipkan diri" ke response biner dan membuat ZIP
        // yang sampai ke user berbeda byte-nya dari file aslinya (=> corrupt
        // walau file di server sendiri valid).
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        return response()
            ->download($zipPath, $zipName, [
                'Content-Type'   => 'application/zip',
                'Content-Length' => filesize($zipPath),
            ])
            ->deleteFileAfterSend(true);
    }

    /* =====================================================================
     | PRIVATE: Normalisasi input filter (checkbox) menjadi array bersih.
     |
     | Menangani semua bentuk yang mungkin dikirim frontend:
     |  - array biasa: ['A','B']
     |  - string tunggal: "B"
     |  - string koma: "A,B,C"
     |  - nilai "semua" ikut terpilih -> dianggap TANPA filter (semua data)
     ===================================================================== */
    private function normalizeFilterArray($input): array
    {
        if (is_array($input)) {
            $clean = array_values(array_filter($input, fn($v) => $v !== null && $v !== ''));
        } elseif (is_string($input) && $input !== '') {
            $clean = str_contains($input, ',')
                ? array_values(array_filter(array_map('trim', explode(',', $input))))
                : [$input];
        } else {
            $clean = [];
        }

        // Kalau salah satu opsi yang dikirim adalah literal "semua"/"all",
        // treat sebagai tanpa filter sama sekali (ambil semua data), bukan
        // whereIn('kolom', ['semua']) yang justru menghasilkan 0 baris cocok
        // — atau di sisi lain, membuat filter lain jadi tidak konsisten.
        $lower = array_map('strtolower', $clean);
        if (in_array('semua', $lower) || in_array('all', $lower)) {
            return [];
        }

        return $clean;
    }

    /* =====================================================================
     | PRIVATE: Ambil array file_surat dengan aman, apa pun bentuk aslinya
     | di database (array ter-cast, string JSON, atau string tunggal).
     |
     | Ini penyebab utama arsip surat "hilang/rusak": sebelumnya kode
     | mengasumsikan file_surat SELALU array PHP. Kalau model Surat tidak
     | (atau belum) meng-cast kolom ini sebagai 'array', Eloquent akan
     | mengembalikan string JSON mentah, is_array() jadi false, dan surat
     | tsb dilewati total tanpa file-nya pernah dicoba dimasukkan ke ZIP.
     ===================================================================== */
    private function extractFileList($rawValue): array
    {
        if (is_array($rawValue)) {
            return $rawValue;
        }

        if (is_string($rawValue) && $rawValue !== '') {
            $decoded = json_decode($rawValue, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? $decoded : [$decoded];
            }
            // Bukan JSON valid — anggap sebagai satu path tunggal.
            return [$rawValue];
        }

        return [];
    }

    /* =====================================================================
     | PRIVATE: Tambahkan file surat ke dalam ZIP
     |
     | Struktur di dalam ZIP (sesuai permintaan): tahun -> bulan -> jenis
     |   arsip/surat/2026/07_Juli/Masuk/SM-001_perihal.pdf
     |   arsip/surat/2026/07_Juli/Keluar/SK-001_perihal.pdf
     ===================================================================== */
    private function addSuratToZip(ZipArchive $zip, array $tahunFilter, string $jenisFilter): array
    {
        $bulanNama = [
            '01' => '01_Januari',  '02' => '02_Februari', '03' => '03_Maret',
            '04' => '04_April',    '05' => '05_Mei',      '06' => '06_Juni',
            '07' => '07_Juli',     '08' => '08_Agustus',  '09' => '09_September',
            '10' => '10_Oktober',  '11' => '11_November', '12' => '12_Desember',
        ];

        // Ambil SEMUA surat (tidak hanya yang punya file_surat non-null di
        // query awal) supaya kita juga bisa mencatat mana yang benar-benar
        // tidak punya lampiran vs mana yang punya tapi filenya hilang.
        // withoutGlobalScopes() WAJIB di sini — backup adalah fitur admin,
        // jadi tidak boleh diam-diam terpotong oleh scope milik user/role
        // tertentu (mis. scope "hanya surat unit saya").
        $query = Surat::query()->withoutGlobalScopes();

        if (!empty($tahunFilter)) {
            $query->whereIn(DB::raw('YEAR(tanggal_surat)'), $tahunFilter);
        }

        if (!empty($jenisFilter) && strtolower($jenisFilter) !== 'semua') {
            $query->whereRaw('LOWER(jenis_surat) = ?', [strtolower($jenisFilter)]);
        }

        $suratList = $query->get();
        $added     = 0;
        $missing   = [];
        $addedDiskPaths = [];
        $usedEntryNames = [];
        $allSuratFilesCache = null;

        foreach ($suratList as $surat) {
            $files = $this->extractFileList($surat->file_surat);
            if (empty($files)) continue;

            $tanggal = $surat->tanggal_surat ? Carbon::parse($surat->tanggal_surat) : now();
            $tahun    = $tanggal->format('Y');
            $bulan    = $tanggal->format('m');
            $jenisDir = ucfirst(strtolower($surat->jenis_surat ?? 'masuk'));
            $folder   = "arsip/surat/{$tahun}/" . ($bulanNama[$bulan] ?? $bulan) . "/{$jenisDir}";

            foreach ($files as $filePath) {
                if (!is_string($filePath) || empty($filePath)) continue;

                $targetRelPath = null;

                if (Storage::disk('public')->exists($filePath)) {
                    $targetRelPath = $filePath;
                } else {
                    $baseName = basename($filePath);
                    $altPath1 = "surat/{$tahun}/{$bulan}/{$jenisDir}/{$baseName}";

                    if (Storage::disk('public')->exists($altPath1)) {
                        $targetRelPath = $altPath1;
                    } else {
                        if ($allSuratFilesCache === null) {
                            $allSuratFilesCache = Storage::disk('public')->allFiles('surat');
                        }
                        // 1. Coba pencarian persis nama file
                        foreach ($allSuratFilesCache as $f) {
                            if (basename($f) === $baseName) {
                                $targetRelPath = $f;
                                break;
                            }
                        }
                        // 2. Jika belum ketemu, coba pencarian awalan (prefix nomor surat)
                        if (!$targetRelPath) {
                            $prefixNo = Str::slug($surat->no_surat ?? '', '');
                            if (!empty($prefixNo)) {
                                foreach ($allSuratFilesCache as $f) {
                                    $diskBase = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', basename($f)));
                                    if (str_starts_with($diskBase, strtolower($prefixNo))) {
                                        $targetRelPath = $f;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                if (!$targetRelPath) {
                    $missing[] = "SURAT #{$surat->id} ({$surat->no_surat}): {$filePath}";
                    continue;
                }

                if (in_array($targetRelPath, $addedDiskPaths)) continue;

                $absPath = storage_path('app/public/' . $targetRelPath);
                $ext     = pathinfo($targetRelPath, PATHINFO_EXTENSION);
                $noSurat = Str::slug($surat->no_surat ?? 'nosurat', '-');
                $perihal = Str::slug(Str::limit($surat->perihal ?? '', 30, ''), '-');
                $niceName = strtolower($jenisDir) . "_{$noSurat}_{$perihal}.{$ext}";

                // Cegah dua entri dengan nama sama persis di folder yang sama
                // di dalam ZIP (mis. dua surat dengan no_surat/perihal yang
                // sama). Beberapa aplikasi ZIP (WinRAR/Windows Explorer)
                // menandai file seperti ini sebagai "rusak/tidak valid" saat
                // diekstrak, meskipun secara teknis ZIP-nya tetap terbuka.
                $entryPath = "{$folder}/{$niceName}";
                if (isset($usedEntryNames[$entryPath])) {
                    $usedEntryNames[$entryPath]++;
                    $niceName  = strtolower($jenisDir) . "_{$noSurat}_{$perihal}_{$usedEntryNames[$entryPath]}.{$ext}";
                    $entryPath = "{$folder}/{$niceName}";
                } else {
                    $usedEntryNames[$entryPath] = 1;
                }

                if (@file_exists($absPath) && @is_readable($absPath)) {
                    $zip->addFile($absPath, $entryPath);
                    $added++;
                    $addedDiskPaths[] = $targetRelPath;
                } else {
                    $missing[] = "SURAT #{$surat->id} ({$surat->no_surat}): {$targetRelPath} (tercatat ada, tapi tidak terbaca di disk)";
                }
            }
        }

        return ['added' => $added, 'missing' => $missing];
    }

    /* =====================================================================
     | PRIVATE: Tambahkan file peserta didik ke dalam ZIP
     |
     | Struktur di dalam ZIP:
     |   arsip/peserta_didik/2023-2024/A/budi_santoso/file_kk_budi_A_2023-2024.pdf
     ===================================================================== */
    private function addPesertaToZip(ZipArchive $zip, array $tahunFilter, array $rombelFilter): array
    {
        $fileFields = [
            'file_kk'         => 'KK',
            'file_akte'       => 'Akte',
            'file_ktp'        => 'KTP',
            'file_ijazah_smp' => 'Ijazah_SMP',
            'file_kip'        => 'KIP',
        ];

        // withoutGlobalScopes() WAJIB di sini juga — kalau tidak, dan model
        // PesertaDidik punya global scope (mis. terikat ke rombel/wali kelas
        // user yang login), maka backup ADMIN diam-diam hanya akan berisi
        // siswa dari rombel itu saja walaupun tidak ada filter rombel yang
        // dicentang. Ini penyebab paling umum dari "kok cuma rombel B" /
        // "kok cuma 1 siswa yang ke-backup padahal totalnya 3".
        $query = PesertaDidik::query()->withoutGlobalScopes();

        if (!empty($tahunFilter)) {
            $query->whereIn('tahun_angkatan', $tahunFilter);
        }

        if (!empty($rombelFilter)) {
            $query->whereIn('rombel', $rombelFilter);
        }

        $pesertaList = $query->get();
        $added       = 0;
        $missing     = [];
        $addedDiskPaths = [];

        foreach ($pesertaList as $peserta) {
            $tahun  = $peserta->tahun_angkatan;
            $rombel = $peserta->rombel ?: 'Tanpa_Rombel';
            $slug   = Str::slug($peserta->nama_peserta_didik ?? 'siswa', '_');
            $folder = "arsip/peserta_didik/{$tahun}/{$rombel}/{$slug}";

            $storageFolder = 'peserta_didik/' . $tahun . '/' . $rombel . '/' . $slug;
            $foundAnyForThisStudent = false;

            // 1. Ambil file sesuai field terdaftar di DB
            foreach ($fileFields as $field => $label) {
                $storedName = $peserta->$field;
                if (!$storedName) continue;

                $targetRelPath = null;
                $rel1 = "{$storageFolder}/{$storedName}";
                $rel2 = "peserta_didik/{$storedName}";

                if (Storage::disk('public')->exists($rel1)) {
                    $targetRelPath = $rel1;
                } elseif (Storage::disk('public')->exists($rel2)) {
                    $targetRelPath = $rel2;
                } elseif (Storage::disk('public')->exists($storedName)) {
                    $targetRelPath = $storedName;
                }

                if (!$targetRelPath) {
                    $missing[] = "PESERTA DIDIK #{$peserta->id_peserta_didik} ({$peserta->nama_peserta_didik}) - {$label}: {$storedName}";
                    continue;
                }

                if (in_array($targetRelPath, $addedDiskPaths)) continue;

                $absPath  = storage_path('app/public/' . $targetRelPath);
                $ext      = pathinfo($storedName, PATHINFO_EXTENSION);
                $niceName = "{$field}_{$slug}_{$rombel}_{$tahun}.{$ext}";

                if (@file_exists($absPath) && @is_readable($absPath)) {
                    $zip->addFile($absPath, "{$folder}/{$niceName}");
                    $added++;
                    $foundAnyForThisStudent = true;
                    $addedDiskPaths[] = $targetRelPath;
                } else {
                    $missing[] = "PESERTA DIDIK #{$peserta->id_peserta_didik} ({$peserta->nama_peserta_didik}) - {$label}: {$targetRelPath} (tercatat ada, tapi tidak terbaca di disk)";
                }
            }

            // 2. Sapu juga seluruh file fisik yang berada di folder storage siswa
            //    (menangkap file yang ada di disk tapi belum tercatat rapi di kolom DB)
            if (Storage::disk('public')->exists($storageFolder)) {
                $folderFiles = Storage::disk('public')->files($storageFolder);
                foreach ($folderFiles as $f) {
                    if (in_array($f, $addedDiskPaths)) continue;

                    $absPath = storage_path('app/public/' . $f);
                    if (@file_exists($absPath) && @is_readable($absPath)) {
                        $baseName = basename($f);
                        $zip->addFile($absPath, "{$folder}/{$baseName}");
                        $added++;
                        $foundAnyForThisStudent = true;
                        $addedDiskPaths[] = $f;
                    }
                }
            }

            if (!$foundAnyForThisStudent) {
                // Buatkan entri folder kosong di dalam ZIP untuk siswa ini
                // agar struktur 3 siswa (Rany, Hasan, Cika) selalu lengkap terwujud di ZIP
                $zip->addEmptyDir($folder);
                $missing[] = "PESERTA DIDIK #{$peserta->id_peserta_didik} ({$peserta->nama_peserta_didik}, rombel {$rombel}, {$tahun}): Folder dikemas di ZIP (berkas terlampir belum diunggah di database).";
            }
        }

        return ['added' => $added, 'missing' => $missing];
    }

    /* =====================================================================
     | PRIVATE: Hitung jumlah & total ukuran file surat
     ===================================================================== */
    private function countFileSurat(): array
    {
        $count = 0;
        $bytes = 0;

        $allSuratDiskFiles = Storage::disk('public')->allFiles('surat');
        $suratList = Surat::withoutGlobalScopes()->get();

        foreach ($suratList as $surat) {
            $files = $this->extractFileList($surat->file_surat);
            if (empty($files)) continue;

            $tanggal  = $surat->tanggal_surat ? Carbon::parse($surat->tanggal_surat) : now();
            $tahun    = $tanggal->format('Y');
            $bulan    = $tanggal->format('m');
            $jenisDir = ucfirst(strtolower($surat->jenis_surat ?? 'masuk'));

            foreach ($files as $path) {
                if (!is_string($path) || empty($path)) continue;

                $targetRelPath = null;
                if (Storage::disk('public')->exists($path)) {
                    $targetRelPath = $path;
                } else {
                    $baseName = basename($path);
                    $altPath1 = "surat/{$tahun}/{$bulan}/{$jenisDir}/{$baseName}";
                    if (Storage::disk('public')->exists($altPath1)) {
                        $targetRelPath = $altPath1;
                    } else {
                        foreach ($allSuratDiskFiles as $f) {
                            if (basename($f) === $baseName) {
                                $targetRelPath = $f;
                                break;
                            }
                        }
                        if (!$targetRelPath) {
                            $prefixNo = Str::slug($surat->no_surat ?? '', '');
                            if (!empty($prefixNo)) {
                                foreach ($allSuratDiskFiles as $f) {
                                    $diskBase = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', basename($f)));
                                    if (str_starts_with($diskBase, strtolower($prefixNo))) {
                                        $targetRelPath = $f;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($targetRelPath) {
                    $count++;
                    try {
                        $bytes += Storage::disk('public')->size($targetRelPath);
                    } catch (\Throwable) {}
                }
            }
        }

        return ['count' => $count, 'size' => $this->formatBytes($bytes)];
    }

    /* =====================================================================
     | PRIVATE: Hitung jumlah & total ukuran file peserta didik
     ===================================================================== */
    private function countFilePeserta(): array
    {
        $fields = ['file_kk', 'file_akte', 'file_ktp', 'file_ijazah_smp', 'file_kip'];
        $count  = 0;
        $bytes  = 0;
        $countedPaths = [];

        $pesertaList = PesertaDidik::withoutGlobalScopes()
            ->get(array_merge($fields, ['tahun_angkatan', 'rombel', 'nama_peserta_didik', 'id_peserta_didik']));

        foreach ($pesertaList as $peserta) {
            $slug          = Str::slug($peserta->nama_peserta_didik ?? '', '_');
            $storageFolder = 'peserta_didik/' . $peserta->tahun_angkatan . '/' . $peserta->rombel . '/' . $slug;

            foreach ($fields as $field) {
                $storedName = $peserta->$field;
                if (!$storedName) continue;

                $targetRelPath = null;
                $rel1 = "{$storageFolder}/{$storedName}";
                $rel2 = "peserta_didik/{$storedName}";

                if (Storage::disk('public')->exists($rel1)) {
                    $targetRelPath = $rel1;
                } elseif (Storage::disk('public')->exists($rel2)) {
                    $targetRelPath = $rel2;
                } elseif (Storage::disk('public')->exists($storedName)) {
                    $targetRelPath = $storedName;
                }

                if ($targetRelPath && !in_array($targetRelPath, $countedPaths)) {
                    $count++;
                    $countedPaths[] = $targetRelPath;
                    try {
                        $bytes += Storage::disk('public')->size($targetRelPath);
                    } catch (\Throwable) {}
                }
            }

            if (Storage::disk('public')->exists($storageFolder)) {
                $folderFiles = Storage::disk('public')->files($storageFolder);
                foreach ($folderFiles as $f) {
                    if (in_array($f, $countedPaths)) continue;

                    $count++;
                    $countedPaths[] = $f;
                    try {
                        $bytes += Storage::disk('public')->size($f);
                    } catch (\Throwable) {}
                }
            }
        }

        return ['count' => $count, 'size' => $this->formatBytes($bytes)];
    }

    /* =====================================================================
     | PRIVATE: Format bytes ke human-readable
     ===================================================================== */
    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }
}