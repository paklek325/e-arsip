<?php

namespace App\Http\Controllers;

use App\Models\PesertaDidik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StorePesertaDidikRequest;
use App\Http\Requests\UpdatePesertaDidikRequest;
use Illuminate\Support\Str;
use ZipArchive;

class PesertaDidikController extends Controller
{
    /**
     * Field dokumen yang digunakan seluruh sistem
     */
    private function fileFields(): array
    {
        return [
            'file_kk',
            'file_akte',
            'file_ktp',
            'file_ijazah_smp',
            'file_kip',
        ];
    }

    /**
     * Path folder penyimpanan peserta_didik
     * peserta_didik/<tahun>/<rombel>/<slug-nama>
     */
    private function pathFolder(PesertaDidik $peserta_didik): string
    {
        return 'peserta_didik/' . $peserta_didik->tahun_angkatan . '/' . $peserta_didik->rombel . '/' . Str::slug($peserta_didik->nama_peserta_didik);
    }

    /**
     * Nama file yang rapi, misal:
     * file_ppdb_hasan_saputra_A_2023.pdf
     */
    private function niceFileName(PesertaDidik $peserta_didik, string $field, string $storedName): string
    {
        $ext = pathinfo($storedName, PATHINFO_EXTENSION);

        return sprintf(
            '%s_%s_%s_%s.%s',
            $field,
            Str::slug($peserta_didik->nama_peserta_didik, '_'),
            $peserta_didik->rombel,
            $peserta_didik->tahun_angkatan,
            $ext
        );
    }

    // ================================================
    // CRUD FUNCTIONS
    // ================================================

    /**
     * INDEX - Tampilkan daftar peserta_didik
     */
    public function index(Request $request)
    {
        $peserta_didik = PesertaDidik::query()
            ->when($request->search, function ($q, $s) {
                $sLower = strtolower($s);
                $like   = "%{$sLower}%";

                $q->where(function ($q) use ($like, $sLower) {
                    $q->whereRaw('LOWER(nama_peserta_didik) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(tempat_lahir) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(rombel) LIKE ?', [$like])
                        ->orWhere('tahun_angkatan', 'LIKE', $like);

                    if (in_array($sLower, ['l', 'laki', 'laki-laki', 'lk'])) {
                        $q->orWhere('jenis_kelamin', 'L');
                    } elseif (in_array($sLower, ['p', 'perempuan', 'pr'])) {
                        $q->orWhere('jenis_kelamin', 'P');
                    }
                });
            })
            ->when($request->rombel, fn($q, $r) => $q->where('rombel', $r))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            // ── Filter tambahan hasil AI Search (nama, jenis_kelamin, tahun_angkatan) ──
            ->when($request->nama, function ($q, $nama) {
                $q->whereRaw('LOWER(nama_peserta_didik) LIKE ?', ['%' . strtolower($nama) . '%']);
            })
            ->when($request->jenis_kelamin, fn($q, $jk) => $q->where('jenis_kelamin', strtoupper($jk)))
            ->when($request->tahun_angkatan, fn($q, $thn) => $q->where('tahun_angkatan', (int) $thn))
            ->when(
                $request->filled('sort_angkatan'),
                function ($q) use ($request) {
                    $q->orderBy('tahun_angkatan', $request->sort_angkatan === 'terbaru' ? 'DESC' : 'ASC')
                      ->orderBy('rombel', 'ASC')
                      ->orderBy('nama_peserta_didik', 'ASC');
                },
                function ($q) use ($request) {
                    // sort_nama: az (default) atau za
                    $namaDir = $request->sort_nama === 'za' ? 'DESC' : 'ASC';
                    $q->orderBy('rombel', 'ASC')
                      ->orderBy('nama_peserta_didik', $namaDir);
                }
            )
            ->paginate(10);

        // ── Ringkasan sesuai filter aktif ──
        $ringkasanQuery = PesertaDidik::query()
            ->when($request->search, function ($q, $s) {
                $like = '%' . strtolower($s) . '%';
                $q->where(function ($q) use ($like, $s) {
                    $sLower = strtolower($s);
                    $q->whereRaw('LOWER(nama_peserta_didik) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(rombel) LIKE ?', [$like])
                        ->orWhere('tahun_angkatan', 'LIKE', $like);
                    if (in_array($sLower, ['l', 'laki', 'laki-laki', 'lk'])) {
                        $q->orWhere('jenis_kelamin', 'L');
                    } elseif (in_array($sLower, ['p', 'perempuan', 'pr'])) {
                        $q->orWhere('jenis_kelamin', 'P');
                    }
                });
            })
            ->when($request->rombel, fn($q, $r) => $q->where('rombel', $r))
            ->when($request->status,  fn($q, $s) => $q->where('status', $s))
            ->when($request->nama, function ($q, $nama) {
                $q->whereRaw('LOWER(nama_peserta_didik) LIKE ?', ['%' . strtolower($nama) . '%']);
            })
            ->when($request->jenis_kelamin, fn($q, $jk) => $q->where('jenis_kelamin', strtoupper($jk)))
            ->when($request->tahun_angkatan, fn($q, $thn) => $q->where('tahun_angkatan', (int) $thn));

        $ringkasan = [
            'total'         => $ringkasanQuery->count(),
            'per_rombel'    => (clone $ringkasanQuery)
                ->selectRaw('rombel, count(*) as total')
                ->whereNotNull('rombel')
                ->where('rombel', '!=', '')
                ->groupBy('rombel')
                ->orderBy('rombel')
                ->pluck('total', 'rombel'),
            'laki'          => (clone $ringkasanQuery)->where('jenis_kelamin', 'L')->count(),
            'perempuan'     => (clone $ringkasanQuery)->where('jenis_kelamin', 'P')->count(),
            'lengkap'       => (clone $ringkasanQuery)->where('status', 'lengkap')->count(),
            'belum_lengkap' => (clone $ringkasanQuery)->where('status', 'belum lengkap')->count(),
        ];

        if ($request->ajax()) {
            return view('peserta_didik.partials.table', compact('peserta_didik', 'ringkasan'))->render();
        }

        return view('peserta_didik.index', compact('peserta_didik', 'ringkasan'));
    }

    /**
     * CEK DUPLIKAT - Cek apakah peserta_didik sudah ada
     */
    public function cekDuplikat(Request $request)
    {
        $nama = trim(strtolower($request->nama_peserta_didik));
        $tanggal = $request->tanggal_lahir;
        $excludeId = $request->exclude_id;

        if (!$nama || !$tanggal) {
            return response()->json(['exists' => false]);
        }

        $query = PesertaDidik::whereRaw('LOWER(nama_peserta_didik) = ?', [$nama])
            ->whereDate('tanggal_lahir', $tanggal);

        if ($excludeId) {
            $query->where('id_peserta_didik', '!=', $excludeId);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function aiSearch(Request $request)
    {
        try {

            $keyword = trim($request->input('keyword', ''));

            if ($keyword === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Keyword kosong',
                ], 422);
            }

            $prompt = <<<'PROMPT'
Anda adalah AI pencarian data peserta_didik untuk sistem arsip sekolah.
 
Tugas Anda: ubah kalimat pencarian berbahasa Indonesia menjadi JSON filter.
Isi HANYA field yang benar-benar disebutkan atau dapat disimpulkan dengan jelas.
 
PENJELASAN FIELD (semua opsional):
- nama        : nama peserta_didik atau bagian dari nama (string)
- rombel      : rombongan belajar, "A" atau "B" (huruf kapital)
- jenis_kelamin : "L" (laki-laki) atau "P" (perempuan)
- tahun_angkatan : tahun masuk/angkatan peserta_didik (integer 4 digit, misal 2023)
- status      : "lengkap" atau "belum lengkap" (status kelengkapan berkas)

ATURAN:
1. tahun_angkatan HARUS integer, bukan string
2. rombel HARUS "A" atau "B" (huruf kapital), jangan nilai lain
3. jenis_kelamin HARUS "L" atau "P"
4. status HARUS persis "lengkap" atau "belum lengkap"
5. nama: ambil nama saja, bukan gelar atau sapaan (misal: dari "cari peserta_didik bernama Budi" → "Budi")
6. Output HARUS JSON valid saja — tanpa markdown, tanpa penjelasan

CONTOH:
Input: peserta_didik rombel A angkatan 2023
Output: {"rombel":"A","tahun_angkatan":2023}

Input: anak perempuan rombel B
Output: {"rombel":"B","jenis_kelamin":"P"}

Input: cari Budi
Output: {"nama":"Budi"}

Input: peserta_didik yang berkasnya belum lengkap
Output: {"status":"belum lengkap"}

Input: laki-laki angkatan 2022 rombel A
Output: {"rombel":"A","jenis_kelamin":"L","tahun_angkatan":2022}

Input: peserta_didik bernama Rina Wati rombel B
Output: {"nama":"Rina Wati","rombel":"B"}

Input: berkas sudah lengkap angkatan 2024
Output: {"status":"lengkap","tahun_angkatan":2024}
 
Sekarang proses input berikut:
 
Input:
PROMPT
                . $keyword . "\n\nHanya tampilkan JSON, tanpa teks lain.";

            $response = Http::timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.groq.key'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => 'openai/gpt-oss-20b',
                    'temperature' => 0,
                    'messages'    => [
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMsg  = $errorBody['error']['message'] ?? $response->body();

                Log::error('Groq API (PesertaDidik) gagal', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Groq API gagal (' . $response->status() . '): ' . $errorMsg,
                ], 500);
            }

            $text = $response['choices'][0]['message']['content'] ?? '{}';

            // Bersihkan markdown fence jika ada
            $text = str_replace(['```json', '```'], '', $text);

            // Ambil objek JSON pertama yang ditemukan
            preg_match('/\{.*\}/s', $text, $match);

            $json = json_decode($match[0] ?? '{}', true);

            if (!is_array($json)) {
                $json = [];
            }

            // Normalisasi tipe data
            if (isset($json['tahun_angkatan'])) {
                $json['tahun_angkatan'] = (int) $json['tahun_angkatan'];
            }

            // Normalisasi rombel → harus A atau B
            if (isset($json['rombel'])) {
                $r = strtoupper(trim($json['rombel']));
                $json['rombel'] = in_array($r, ['A', 'B']) ? $r : null;
                if ($json['rombel'] === null) unset($json['rombel']);
            }

            // Normalisasi jenis_kelamin → L atau P
            if (isset($json['jenis_kelamin'])) {
                $jk = strtoupper(trim($json['jenis_kelamin']));
                $json['jenis_kelamin'] = in_array($jk, ['L', 'P']) ? $jk : null;
                if ($json['jenis_kelamin'] === null) unset($json['jenis_kelamin']);
            }

            // Normalisasi status → persis "lengkap" atau "belum lengkap"
            if (isset($json['status'])) {
                $st = strtolower(trim($json['status']));
                $json['status'] = in_array($st, ['lengkap', 'belum lengkap']) ? $st : null;
                if ($json['status'] === null) unset($json['status']);
            }

            // Izinkan hanya field yang dikenal
            $allowedKeys = ['nama', 'rombel', 'jenis_kelamin', 'tahun_angkatan', 'status'];
            $json = array_filter(
                array_intersect_key($json, array_flip($allowedKeys)),
                fn($v) => $v !== null && $v !== ''
            );

            return response()->json([
                'success' => true,
                'data'    => $json,
            ]);
        } catch (\Throwable $e) {

            Log::error('AI Search PesertaDidik Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'AI Search gagal',
            ], 500);
        }
    }


    /**
     * STORE - Tambah peserta_didik baru
     */
    public function store(StorePesertaDidikRequest $request)
    {

        try {
            $peserta_didik = PesertaDidik::create(array_merge(
                $request->only([
                    'nama_peserta_didik',
                    'jenis_kelamin',
                    'alamat',
                    'rombel',
                    'tahun_angkatan',
                    'tempat_lahir',
                    'tanggal_lahir',
                ]),
                ['id_user' => Auth::check() ? Auth::id() : null]
            ));

            $folder = $this->pathFolder($peserta_didik);

            // pastikan folder ada di disk public
            Storage::disk('public')->makeDirectory($folder);

            foreach ($this->fileFields() as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $fileName = $field . '_' . time() . '.' . $file->getClientOriginalExtension();

                    // simpan di storage/app/public/peserta_didik/...
                    $file->storeAs($folder, $fileName, 'public');

                    $peserta_didik->update([$field => $fileName]);
                }
            }

            return response()->json(['success' => true, 'message' => 'PesertaDidik berhasil ditambahkan!']);
        } catch (\Exception $e) {

            if (isset($peserta_didik) && $peserta_didik->exists) {
                Storage::disk('public')->deleteDirectory($this->pathFolder($peserta_didik));
                $peserta_didik->delete();
            }

            return response()->json([
                'success' => false,
                'message' => "Gagal menyimpan: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * SHOW - Tampilkan detail peserta_didik (untuk AJAX)
     */
    public function show(PesertaDidik $peserta_didik)
    {
        $folder = $this->pathFolder($peserta_didik);
        $data = $peserta_didik->toArray();

        foreach ($this->fileFields() as $field) {
            $data[$field . '_url'] = $peserta_didik->$field
                ? asset("storage/{$folder}/{$peserta_didik->$field}")
                : null;
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * UPDATE - Update data peserta_didik
     */
    public function update(UpdatePesertaDidikRequest $request, PesertaDidik $peserta_didik)
    {

        $oldFolder = $this->pathFolder($peserta_didik);

        $peserta_didik->update($request->only([
            'nama_peserta_didik',
            'jenis_kelamin',
            'alamat',
            'rombel',
            'tahun_angkatan',
            'tempat_lahir',
            'tanggal_lahir'
        ]));

        $newFolder = $this->pathFolder($peserta_didik);

        // Jika folder berubah, pindahkan file di disk 'public'
        if ($oldFolder !== $newFolder) {
            $disk = Storage::disk('public');

            if ($disk->exists($oldFolder)) {
                $disk->makeDirectory($newFolder);

                foreach ($disk->allFiles($oldFolder) as $file) {
                    $relative = Str::after($file, $oldFolder . '/');
                    if (!$relative) $relative = basename($file);

                    $disk->copy($file, $newFolder . '/' . $relative);
                }

                $disk->deleteDirectory($oldFolder);
            }
        }

        // Hapus file yang ditandai delete
        foreach ($this->fileFields() as $field) {
            if ($request->get("{$field}_delete") == "1") {
                $filename = $peserta_didik->$field;

                if ($filename && Storage::disk('public')->exists($newFolder . '/' . $filename)) {
                    Storage::disk('public')->delete($newFolder . '/' . $filename);
                }

                $peserta_didik->update([$field => null]);
            }
        }

        // pastikan folder baru ada
        Storage::disk('public')->makeDirectory($newFolder);

        // Upload file baru
        foreach ($this->fileFields() as $field) {
            if ($request->hasFile($field)) {
                $old = $peserta_didik->$field;
                if ($old && Storage::disk('public')->exists($newFolder . '/' . $old)) {
                    Storage::disk('public')->delete($newFolder . '/' . $old);
                }

                $file = $request->file($field);
                $fileName = $field . '_' . time() . '.' . $file->getClientOriginalExtension();

                // simpan ke storage/app/public/peserta_didik/...
                $file->storeAs($newFolder, $fileName, 'public');

                $peserta_didik->update([$field => $fileName]);
            }
        }

        return response()->json(['success' => true, 'message' => 'PesertaDidik berhasil diperbarui!']);
    }

    /**
     * DELETE - Hapus peserta_didik
     */
    public function destroy(PesertaDidik $peserta_didik)
    {
        Storage::disk('public')->deleteDirectory($this->pathFolder($peserta_didik));
        $peserta_didik->delete();

        return response()->json(['success' => true, 'message' => 'Data peserta_didik berhasil dihapus']);
    }

    // ================================================
    // DOWNLOAD FUNCTIONS
    // ================================================

    /**
     * DOWNLOAD FILE SATUAN
     */
    public function download(Request $request, $id, $field)
    {
        $peserta_didik = PesertaDidik::findOrFail($id);

        if (!in_array($field, $this->fileFields())) {
            return response()->json(['success' => false, 'message' => 'Field file tidak valid'], 400);
        }

        $folder   = $this->pathFolder($peserta_didik);
        $fileName = $peserta_didik->$field;

        if (!$fileName) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan'], 404);
        }

        // path fisik file di storage
        $fullPath = storage_path("app/public/{$folder}/{$fileName}");

        if (!file_exists($fullPath)) {
            return response()->json(['success' => false, 'message' => 'File tidak ada di server'], 404);
        }

        $mime          = mime_content_type($fullPath);
        $downloadName  = $this->niceFileName($peserta_didik, $field, $fileName);
        $forceDownload = $request->boolean('download', false);

        if (Str::startsWith($mime, 'image/') || $mime === 'application/pdf') {
            $disposition = $forceDownload ? 'attachment' : 'inline';

            return response()->file($fullPath, [
                'Content-Type'        => $mime,
                'Content-Disposition' => $disposition . '; filename="' . $downloadName . '"',
            ]);
        }

        return response()->download($fullPath, $downloadName);
    }

    /**
     * DOWNLOAD SEMUA FILE ZIP
     */
    public function downloadAll(PesertaDidik $peserta_didik)
    {
        $folder = $this->pathFolder($peserta_didik);

        $zipDownloadName = sprintf(
            '%s_%s_%s.zip',
            Str::slug($peserta_didik->nama_peserta_didik, '_'),
            $peserta_didik->rombel,
            $peserta_didik->tahun_angkatan
        );

        $zipName = 'tmp_' . uniqid() . '_' . $zipDownloadName;
        $tempDir = 'temp';

        Storage::disk('local')->makeDirectory($tempDir);
        $zipPath = storage_path("app/{$tempDir}/{$zipName}");

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error("Gagal membuat ZIP: {$zipPath}");
            return response()->json(['success' => false, 'message' => 'Gagal membuat ZIP'], 500);
        }

        $filesAdded = 0;

        foreach ($this->fileFields() as $field) {
            $storedName = $peserta_didik->$field;

            if ($storedName) {
                $relative = "{$folder}/{$storedName}";

                if (Storage::disk('public')->exists($relative)) {
                    $absolute   = storage_path("app/public/{$relative}");

                    if (file_exists($absolute)) {
                        $prettyName = $this->niceFileName($peserta_didik, $field, $storedName);
                        $zip->addFile($absolute, $prettyName);
                        $filesAdded++;
                    }
                }
            }
        }

        $zip->close();

        if ($filesAdded === 0) {
            Storage::disk('local')->delete("{$tempDir}/{$zipName}");
            return response()->json(['success' => false, 'message' => 'Tidak ada file untuk diunduh'], 404);
        }

        return response()
            ->download($zipPath, $zipDownloadName)
            ->deleteFileAfterSend(true);
    }

    /**
     * DOWNLOAD FILE TERPILIH (ZIP)
     */
    public function downloadSelected(Request $request, PesertaDidik $peserta_didik)
    {
        // Ambil parameter fields
        $fieldsParam = $request->get('fields');

        Log::info("=== DOWNLOAD SELECTED START ===");
        Log::info("PesertaDidik ID: {$peserta_didik->id_peserta_didik} - {$peserta_didik->nama_peserta_didik}");
        Log::info("Fields Parameter: " . $fieldsParam);

        if (!$fieldsParam) {
            Log::warning("Parameter fields kosong");
            return response()->json(['success' => false, 'message' => 'Tidak ada field yang dipilih'], 400);
        }

        // Split fields menjadi array dan filter
        $fields = array_filter(array_map('trim', explode(',', $fieldsParam)));
        Log::info("Fields Array: " . print_r($fields, true));

        // Validasi fields
        $validFields = array_intersect($fields, $this->fileFields());
        Log::info("Valid Fields: " . print_r($validFields, true));

        if (empty($validFields)) {
            Log::warning("Tidak ada field yang valid");
            return response()->json(['success' => false, 'message' => 'Field tidak valid'], 400);
        }

        $folder  = $this->pathFolder($peserta_didik);
        $tempDir = 'temp';
        Storage::disk('local')->makeDirectory($tempDir);

        // Buat label untuk nama ZIP
        $labels = array_map(function ($field) {
            return strtoupper(str_replace('file_', '', $field));
        }, $validFields);
        $labelString = implode('-', $labels);

        $zipDownloadName = sprintf(
            '%s_%s_%s_(%s).zip',
            Str::slug($peserta_didik->nama_peserta_didik, '_'),
            $peserta_didik->rombel,
            $peserta_didik->tahun_angkatan,
            $labelString
        );

        $zipName = 'tmp_' . uniqid() . '_' . $zipDownloadName;
        $zipPath = storage_path("app/{$tempDir}/{$zipName}");

        Log::info("ZIP Path: {$zipPath}");

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error("Gagal membuat ZIP file");
            return response()->json(['success' => false, 'message' => 'Gagal membuat ZIP'], 500);
        }

        $filesAdded = 0;
        $filesNotFound = [];

        foreach ($validFields as $field) {
            $storedName = $peserta_didik->$field;

            Log::info("Processing field: {$field}");

            if (!$storedName) {
                Log::warning("  └─ Field {$field}: Nama file kosong di database");
                $filesNotFound[] = $field;
                continue;
            }

            $relative = "{$folder}/{$storedName}";
            Log::info("  └─ Relative path: {$relative}");

            // Cek keberadaan file
            if (!Storage::disk('public')->exists($relative)) {
                Log::error("  └─ FILE TIDAK DITEMUKAN: storage/app/public/{$relative}");
                $filesNotFound[] = $field;
                continue;
            }

            $absolute   = storage_path("app/public/{$relative}");
            $prettyName = $this->niceFileName($peserta_didik, $field, $storedName);

            Log::info("  └─ Absolute path: {$absolute}");
            Log::info("  └─ Pretty name: {$prettyName}");

            // Verifikasi final sebelum add ke ZIP
            if (file_exists($absolute) && is_readable($absolute)) {
                $result = $zip->addFile($absolute, $prettyName);

                if ($result) {
                    $filesAdded++;
                    Log::info("  └─ ✓ BERHASIL ditambahkan ke ZIP");
                } else {
                    Log::error("  └─ ✗ GAGAL menambahkan ke ZIP (addFile returned false)");
                    $filesNotFound[] = $field;
                }
            } else {
                Log::error("  └─ ✗ File tidak dapat dibaca: {$absolute}");
                $filesNotFound[] = $field;
            }
        }

        $zip->close();

        Log::info("=== DOWNLOAD SELECTED END ===");
        Log::info("Total files berhasil: {$filesAdded}");
        Log::info("Total files gagal: " . count($filesNotFound));
        if (!empty($filesNotFound)) {
            Log::info("Files tidak ditemukan: " . implode(', ', $filesNotFound));
        }

        if ($filesAdded === 0) {
            Storage::disk('local')->delete("{$tempDir}/{$zipName}");

            $message = 'Tidak ada file yang dapat diunduh.';
            if (!empty($filesNotFound)) {
                $message .= ' File tidak ditemukan: ' . implode(', ', $filesNotFound);
            }

            return response()->json(['success' => false, 'message' => $message], 404);
        }

        // Jika ada beberapa file yang tidak ditemukan, log warning
        if (!empty($filesNotFound)) {
            Log::warning("Beberapa file tidak ditemukan: " . implode(', ', $filesNotFound));
        }

        return response()
            ->download($zipPath, $zipDownloadName)
            ->deleteFileAfterSend(true);
    }
}
