<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Http\Requests\StoreSuratRequest;
use App\Http\Requests\UpdateSuratRequest;
use Carbon\Carbon;
use ZipArchive;

class SuratController extends Controller
{
    /**
     * Folder penyimpanan file surat
     */
    protected string $storageFolder = 'surat';

    /**
     * Membuat folder berdasarkan Tahun/Bulan/Jenis
     * Contoh:
     * surat/2026/06/Masuk
     * surat/2026/06/Keluar
     */
    protected function getStoragePathByDateAndJenis(
        string $tanggal,
        string $jenis
    ): string {

        $date = Carbon::parse($tanggal);

        return sprintf(
            '%s/%s/%s/%s',
            $this->storageFolder,
            $date->format('Y'),
            $date->format('m'),
            ucfirst($jenis)
        );
    }
    /* ======================================================
 | 1. INDEX
 ====================================================== */
    public function index(Request $request)
    {
        $query = Surat::with([
            'kode',
            'user',
            'editor',
        ]);

        /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */
        if ($request->filled('search') && !$request->filled('tanggal')) {

            $s = strtolower(trim($request->search));

            $bulanMap = [
                'januari'  => '01',
                'februari' => '02',
                'maret'    => '03',
                'april'    => '04',
                'mei'      => '05',
                'juni'     => '06',
                'juli'     => '07',
                'agustus'  => '08',
                'september' => '09',
                'oktober'  => '10',
                'november' => '11',
                'desember' => '12',
            ];

            $bulanMatch = null;

            foreach ($bulanMap as $nama => $kode) {

                if (str_starts_with($nama, $s)) {
                    $bulanMatch = $kode;
                    break;
                }
            }

            if (preg_match('/^\d{4}$/', $s)) {

                $query->whereYear('tanggal_surat', $s);
            } elseif ($bulanMatch) {

                $query->whereMonth('tanggal_surat', $bulanMatch);
            } else {

                $query->where(function ($q) use ($s) {

                    $q->whereRaw('LOWER(no_surat) LIKE ?', ["%{$s}%"])
                        ->orWhereRaw('LOWER(kode_surat) LIKE ?', ["%{$s}%"])
                        ->orWhereRaw('LOWER(perihal) LIKE ?', ["%{$s}%"])
                        ->orWhereRaw('LOWER(instansi) LIKE ?', ["%{$s}%"])
                        ->orWhereRaw('LOWER(pengirim) LIKE ?', ["%{$s}%"])
                        ->orWhereRaw('LOWER(penerima) LIKE ?', ["%{$s}%"])
                        ->orWhereRaw('LOWER(keterangan) LIKE ?', ["%{$s}%"])
                        ->orWhereRaw('LOWER(jenis_surat) LIKE ?', ["%{$s}%"])

                        ->orWhereHas('user', function ($u) use ($s) {

                            $u->whereRaw(
                                'LOWER(name) LIKE ?',
                                ["%{$s}%"]
                            );
                        })

                        ->orWhereHas('editor', function ($u) use ($s) {

                            $u->whereRaw(
                                'LOWER(name) LIKE ?',
                                ["%{$s}%"]
                            );
                        });
                });
            }
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER TANGGAL
    |--------------------------------------------------------------------------
    */

        if ($request->filled('tanggal')) {

            $t = $request->tanggal;

            if (preg_match('/^\d{4}$/', $t)) {

                $query->whereYear('tanggal_surat', $t);
            } elseif (preg_match('/^\d{4}-\d{2}$/', $t)) {

                [$y, $m] = explode('-', $t);

                $query->whereYear('tanggal_surat', $y)
                    ->whereMonth('tanggal_surat', $m);
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $t)) {

                $query->whereDate('tanggal_surat', $t);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER JENIS
    |--------------------------------------------------------------------------
    */

        if ($request->filled('jenis')) {

            $query->where(
                'jenis_surat',
                $request->jenis
            );
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER DARI LAPORAN
    |--------------------------------------------------------------------------
    */

        if ($request->filled('jenis_surat')) {

            $jenis = ucfirst(
                strtolower(
                    trim($request->jenis_surat)
                )
            );

            if (in_array($jenis, ['Masuk', 'Keluar'])) {

                $query->where(
                    'jenis_surat',
                    $jenis
                );
            }
        }

        if ($request->filled('bulan')) {

            $query->whereMonth(
                'tanggal_surat',
                (int)$request->bulan
            );
        }

        if ($request->filled('tahun')) {

            $query->whereYear(
                'tanggal_surat',
                (int)$request->tahun
            );
        }

        /*
    |--------------------------------------------------------------------------
    | SORT
    |--------------------------------------------------------------------------
    */

        switch ($request->get('sort', 'tanggal_terbaru')) {

            case 'tanggal_terlama':

                $query->orderBy(
                    'tanggal_surat',
                    'asc'
                );

                break;

            case 'created_terbaru':

                $query->orderByDesc(
                    'created_at'
                );

                break;

            case 'created_terlama':

                $query->orderBy(
                    'created_at'
                );

                break;

            case 'a-z':

                $query->orderBy(
                    'no_surat'
                );

                break;

            case 'z-a':

                $query->orderByDesc(
                    'no_surat'
                );

                break;

            default:

                $query->orderByDesc(
                    'tanggal_surat'
                );
        }

        $surat = $query
            ->paginate(10)
            ->appends($request->query());

        $kode = Kode::orderBy('kode')->get();

        if ($request->ajax()) {

            return view(
                'surat.partials.table',
                compact('surat')
            )->render();
        }

        return view(
            'surat.index',
            compact(
                'surat',
                'kode'
            )
        );
    }
    /* ======================================================
 | 2. SHOW
 ====================================================== */
    public function show(Surat $surat)
    {
        $surat->load([
            'kode',
            'user',
            'editor',
        ]);

        /*
    |--------------------------------------------------------------------------
    | Lampiran
    |--------------------------------------------------------------------------
    */
        $files = [];

        foreach (($surat->file_surat ?? []) as $file) {

            if (
                is_string($file) &&
                $file !== '' &&
                Storage::disk('public')->exists($file)
            ) {

                $files[] = [
                    'path' => $file,
                    'url'  => asset('storage/' . $file),
                    'name' => basename($file),
                ];
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Status Edit
    |--------------------------------------------------------------------------
    */

        $status = 'Belum Pernah Diubah';

        if (
            $surat->created_at &&
            $surat->updated_at &&
            !$surat->created_at->equalTo($surat->updated_at)
        ) {

            $status = 'Pernah Diubah';
        }

        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        return response()->json([

            'success' => true,

            'surat' => [

                'id' => $surat->id,

                'no_surat' => $surat->no_surat,

                'kode_surat' => $surat->kode_surat,

                'kode_keterangan' => optional(
                    $surat->kode
                )->description,

                'jenis_surat' => $surat->jenis_surat,

                'tanggal_surat' => optional(
                    $surat->tanggal_surat
                )->translatedFormat('d F Y'),

                // Format ISO (Y-m-d) khusus untuk mengisi <input type="date"> di modal Edit.
                // Input type="date" HANYA menerima format YYYY-MM-DD, bukan "01 Juli 2026".
                'tanggal_surat_raw' => optional(
                    $surat->tanggal_surat
                )->format('Y-m-d'),

                'perihal' => $surat->perihal,

                'instansi' => $surat->instansi,

                'pengirim' => $surat->pengirim,

                'penerima' => $surat->penerima,

                'keterangan' => $surat->keterangan,

                /*
            |--------------------------------------------------------------------------
            | Informasi Arsip
            |--------------------------------------------------------------------------
            */

                'created_by' => optional(
                    $surat->user
                )->name,

                'updated_by' => optional(
                    $surat->editor
                )->name,

                'created_at' => optional(
                    $surat->created_at
                )->translatedFormat('d F Y H:i'),

                'updated_at' => optional(
                    $surat->updated_at
                )->translatedFormat('d F Y H:i'),

                'status' => $status,

                /*
            |--------------------------------------------------------------------------
            | Lampiran
            |--------------------------------------------------------------------------
            */

                'files' => $files,

            ],

        ]);
    }
    /* ======================================================
 | 3. STORE
 ====================================================== */
    public function store(StoreSuratRequest $request)
    {
        try {

            $validated = $request->validated();

            /*
        |--------------------------------------------------------------------------
        | KODE SURAT
        |--------------------------------------------------------------------------
        */

            if ($validated['jenis_surat'] === 'Masuk') {

                $validated['kode_surat'] = $request->filled('kode_surat')
                    ? trim($request->kode_surat)
                    : null;

                $keterangan = null;

                // Surat Masuk boleh pakai kode bebas (tidak wajib ada di
                // master), tapi kalau kebetulan cocok, tetap kita rujuk
                // id_kode-nya supaya relasi FK asli ikut terisi.
                $kode = $validated['kode_surat']
                    ? Kode::where('kode', $validated['kode_surat'])->first()
                    : null;
            } else {

                $validated['kode_surat'] = $request->kode_surat;

                $kode = Kode::where(
                    'kode',
                    $validated['kode_surat']
                )->first();

                $keterangan = $kode?->description;
            }

            // id_kode = FOREIGN KEY asli ke master tabel kode (NULL kalau
            // kode_surat tidak/ belum terdaftar di master, mis. Surat
            // Masuk dengan kode bebas dari instansi pengirim).
            $validated['id_kode'] = $kode?->id_kode;

            /*
        |--------------------------------------------------------------------------
        | Upload File
        |--------------------------------------------------------------------------
        */

            $files = [];

            $uploads = $request->file('file_surat', []);

            foreach ($uploads as $file) {

                if (!$file || !$file->isValid()) {
                    continue;
                }

                $ext = $file->getClientOriginalExtension();

                $base = Str::slug(

                    $validated['no_surat']
                        . '-'
                        . ($validated['kode_surat'] ?? 'tanpa-kode')
                        . '-'
                        . $validated['perihal']

                );

                $base = Str::limit(
                    $base,
                    100,
                    ''
                );

                $name =

                    $base

                    . '-'

                    . uniqid()

                    . '.'

                    . $ext;

                $folder = $this->getStoragePathByDateAndJenis(

                    $validated['tanggal_surat'],

                    $validated['jenis_surat']

                );

                $files[] = $file->storeAs(

                    $folder,

                    $name,

                    'public'

                );
            }

            /*
        |--------------------------------------------------------------------------
        | Simpan
        |--------------------------------------------------------------------------
        */

            $surat = Surat::create([

                'no_surat'      => $validated['no_surat'],

                'kode_surat'    => $validated['kode_surat'],

                'id_kode'       => $validated['id_kode'],

                'jenis_surat'   => $validated['jenis_surat'],

                'tanggal_surat' => $validated['tanggal_surat'],

                'perihal'       => $validated['perihal'],

                'instansi'      => $validated['instansi'],

                'pengirim'      => $validated['pengirim'] ?? null,

                'penerima'      => $validated['penerima'] ?? null,

                'keterangan'    => $keterangan,

                'file_surat'    => $files,

                /*
            |--------------------------------------------------------------------------
            | Informasi Arsip
            |--------------------------------------------------------------------------
            */

                'id_user'       => Auth::id(),

                'updated_by'    => Auth::id(),

            ]);

            return response()->json([

                'success' => true,

                'message' => 'Surat berhasil ditambahkan.',

                'data' => $surat,

            ]);
        } catch (ValidationException $e) {

            return response()->json([

                'success' => false,

                'message' => $e->validator
                    ->errors()
                    ->first(),

                'errors' => $e->validator
                    ->errors(),

            ], 422);
        } catch (Throwable $e) {

            Log::error(

                'STORE SURAT',

                [

                    'message' => $e->getMessage(),

                    'trace' => $e->getTraceAsString(),

                ]

            );

            return response()->json([

                'success' => false,

                'message' => 'Terjadi kesalahan saat menyimpan surat.',

            ], 500);
        }
    }
    /* ======================================================
 | 4. UPDATE
 ====================================================== */
    public function update(UpdateSuratRequest $request, Surat $surat)
    {
        try {

            $validated = $request->validated();

            /*
        |--------------------------------------------------------------------------
        | KODE SURAT
        |--------------------------------------------------------------------------
        */

            if ($validated['jenis_surat'] === 'Masuk') {

                $validated['kode_surat'] = $request->filled('kode_surat')
                    ? trim($request->kode_surat)
                    : null;

                $keterangan = null;

                // Surat Masuk boleh pakai kode bebas (tidak wajib ada di
                // master), tapi kalau kebetulan cocok, tetap kita rujuk
                // id_kode-nya supaya relasi FK asli ikut terisi.
                $kode = $validated['kode_surat']
                    ? Kode::where('kode', $validated['kode_surat'])->first()
                    : null;
            } else {

                $validated['kode_surat'] = $request->kode_surat;

                $kode = Kode::where(
                    'kode',
                    $validated['kode_surat']
                )->first();

                $keterangan = $kode?->description;
            }

            // id_kode = FOREIGN KEY asli ke master tabel kode (NULL kalau
            // kode_surat tidak/belum terdaftar di master, mis. Surat Masuk
            // dengan kode bebas dari instansi pengirim).
            $validated['id_kode'] = $kode?->id_kode;

            /*
        |--------------------------------------------------------------------------
        | File Lama
        |--------------------------------------------------------------------------
        */

            $oldFiles = $surat->file_surat ?? [];

            $hapusFile = $request->input(
                'hapus_file',
                []
            );

            /*
        |--------------------------------------------------------------------------
        | Hapus File Lama
        |--------------------------------------------------------------------------
        */

            foreach ($hapusFile as $file) {

                if (
                    is_string($file) &&
                    Storage::disk('public')->exists($file)
                ) {

                    Storage::disk('public')->delete($file);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Folder Baru
        |--------------------------------------------------------------------------
        */

            $folder = $this->getStoragePathByDateAndJenis(

                $validated['tanggal_surat'],

                $validated['jenis_surat']

            );

            /*
        |--------------------------------------------------------------------------
        | Nama Dasar File
        |--------------------------------------------------------------------------
        */

            $base = Str::slug(

                $validated['no_surat']

                    . '-'

                    . ($validated['kode_surat'] ?? 'tanpa-kode')

                    . '-'

                    . $validated['perihal']

            );

            $base = Str::limit(
                $base,
                100,
                ''
            );

            /*
        |--------------------------------------------------------------------------
        | Rename File Lama
        |--------------------------------------------------------------------------
        */

            $keepFiles = [];

            foreach ($oldFiles as $file) {

                if (
                    !is_string($file) ||
                    in_array($file, $hapusFile)
                ) {

                    continue;
                }

                if (!Storage::disk('public')->exists($file)) {

                    continue;
                }

                $ext = pathinfo(
                    $file,
                    PATHINFO_EXTENSION
                );

                $uniq = substr(

                    pathinfo(
                        $file,
                        PATHINFO_FILENAME
                    ),

                    -13

                );

                $newName =

                    $base

                    . '-'

                    . $uniq

                    . '.'

                    . $ext;

                $newPath =

                    $folder

                    . '/'

                    . $newName;

                if ($file != $newPath) {

                    Storage::disk('public')->move(

                        $file,

                        $newPath

                    );
                }

                $keepFiles[] = $newPath;
            }

            /*
        |--------------------------------------------------------------------------
        | Upload File Baru
        |--------------------------------------------------------------------------
        */

            $newFiles = [];

            foreach ($request->file('file_surat', []) as $upload) {

                if (!$upload || !$upload->isValid()) {

                    continue;
                }

                $ext = $upload->getClientOriginalExtension();

                $name =

                    $base

                    . '-'

                    . uniqid()

                    . '.'

                    . $ext;

                $newFiles[] = $upload->storeAs(

                    $folder,

                    $name,

                    'public'

                );
            }

            /*
        |--------------------------------------------------------------------------
        | Update Database
        |--------------------------------------------------------------------------
        */

            $surat->update([

                'no_surat'      => $validated['no_surat'],

                'kode_surat'    => $validated['kode_surat'],

                'id_kode'       => $validated['id_kode'],

                'jenis_surat'   => $validated['jenis_surat'],

                'tanggal_surat' => $validated['tanggal_surat'],

                'perihal'       => $validated['perihal'],

                'instansi'      => $validated['instansi'],

                'pengirim'      => $validated['pengirim'] ?? null,

                'penerima'      => $validated['penerima'] ?? null,

                'keterangan'    => $keterangan,

                'file_surat'    => array_merge(

                    $keepFiles,

                    $newFiles

                ),

                /*
            |--------------------------------------------------------------------------
            | Editor Terakhir
            |--------------------------------------------------------------------------
            */

                'updated_by'    => Auth::id(),

            ]);

            return response()->json([

                'success' => true,

                'message' => 'Surat berhasil diperbarui.',

            ]);
        } catch (ValidationException $e) {

            return response()->json([

                'success' => false,

                'message' => $e->validator
                    ->errors()
                    ->first(),

                'errors' => $e->validator
                    ->errors(),

            ], 422);
        } catch (Throwable $e) {

            Log::error(
                'UPDATE SURAT',
                [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui surat.',
            ], 500);
        }
    }

    /* ======================================================
 | 5. KODE SURAT KELUAR (untuk dropdown di modal Tambah/Edit)
 ====================================================== */
    public function kodeSuratKeluar()
    {
        // JS (fetchKodeList) mengharapkan array JSON polos berisi objek
        // { kode, description }, BUKAN dibungkus { success, data: [...] }.
        $kode = Kode::orderBy('kode')
            ->get(['kode', 'description']);

        return response()->json($kode);
    }

    /* ======================================================
 | 6. CEK DUPLIKAT SURAT
 ====================================================== */
    public function cekDuplikat(Request $request)
    {
        $request->validate([
            'no_surat'    => 'required|string',
            'instansi'    => 'required|string',
            'tanggal_surat' => 'required|string',
            'exclude_id'  => 'nullable|integer',
        ]);

        // tanggal_surat dari form input type="date" selalu format Y-m-d,
        // jadi aman langsung dipakai untuk whereDate.
        $tanggal = $request->tanggal_surat;

        try {
            $tanggalParsed = Carbon::parse($tanggal)->format('Y-m-d');
        } catch (Throwable $e) {
            return response()->json([
                'exists' => false,
            ]);
        }

        $query = Surat::whereRaw('LOWER(no_surat) = ?', [strtolower(trim($request->no_surat))])
            ->whereRaw('LOWER(instansi) = ?', [strtolower(trim($request->instansi))])
            ->whereDate('tanggal_surat', $tanggalParsed);

        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', $request->exclude_id);
        }

        return response()->json([
            'exists' => $query->exists(),
        ]);
    }

    /* ======================================================
 | 7. DOWNLOAD MULTIPLE (ZIP)
 ====================================================== */
    public function downloadMultiple(Request $request)
    {
        $request->validate([
            'surat_id' => 'required|integer',
            'paths'    => 'required|array|min:1',
            'paths.*'  => 'string',
        ]);

        $surat = Surat::findOrFail($request->surat_id);

        // Hanya izinkan path yang memang terdaftar di file_surat milik surat ini,
        // supaya endpoint ini tidak bisa dipakai untuk mengunduh file sembarangan.
        $allowedPaths = collect($surat->file_surat ?? [])
            ->filter(fn($p) => is_string($p))
            ->values();

        $paths = collect($request->paths)
            ->filter(fn($p) => $allowedPaths->contains($p))
            ->values();

        if ($paths->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada file valid yang bisa diunduh.',
            ], 422);
        }

        $zipName = 'surat-' . $surat->id . '-' . now()->format('YmdHis') . '.zip';
        $zipPath = storage_path('app/' . $zipName);

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat file ZIP.',
            ], 500);
        }

        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $zip->addFile(
                    Storage::disk('public')->path($path),
                    basename($path)
                );
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    /* ======================================================
 | 8. AI SEARCH (placeholder)
 |   Belum dipakai oleh surat.js saat ini, disiapkan agar
 |   route tidak 500 jika dipanggil dari fitur lain (mis. chat AI).
 ====================================================== */
    public function aiSearch(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur AI search untuk surat belum diimplementasikan.',
        ], 501);
    }
}
