<?php

namespace App\Http\Controllers;

use App\Models\Kode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KodeController extends Controller
{
    /**
     * Halaman utama daftar kode + pencarian + pagination
     */
    public function index(Request $request)
    {
        $query = Kode::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('kode', 'asc')->paginate(10);

        // Return partial kalau AJAX (untuk reload tabel saja)
        if ($request->ajax()) {
            return view('kode.partials.table', compact('data'))->render();
        }

        return view('kode.index', compact('data'));
    }

    /**
     * Endpoint khusus untuk reload tabel via AJAX (kalau mau terpisah)
     */
    public function list(Request $request)
    {
        $query = Kode::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('kode', 'asc')->paginate(10);

        return view('kode.partials.table', compact('data'))->render();
    }

    /**
     * Ambil detail satu kode (dipakai untuk form edit / detail)
     */
    public function show($id): JsonResponse
    {
        $kode = Kode::findOrFail($id);
        return response()->json($kode);
    }

    /**
     * Cek duplikat kode (dipakai JS realtime)
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $kode     = $request->kode;
        $ignoreId = $request->id;

        if (!$kode) {
            return response()->json(['exists' => false]);
        }

        $query = Kode::where('kode', $kode);

        if ($ignoreId) {
            $query->where('id_kode', '!=', $ignoreId);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    /**
     * Simpan data kode baru
     */
    public function store(Request $request): JsonResponse
    {
        $messages = [
            'kode.required' => 'Kolom Kode wajib diisi.',
            'kode.max'      => 'Kolom Kode maksimal 10 karakter.',
        ];

        // Validasi dasar
        $request->validate([
            'kode'        => 'required|max:10',
            'description' => 'nullable|string|max:255',
        ], $messages);

        // Cek duplikat manual
        $exists = Kode::where('kode', $request->kode)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => [
                    'kode' => ['Kode telah tersedia.'],
                ],
            ], 422);
        }

        // Eloquent create → INI yang penting supaya event model tetap kepakai
        $kode = Kode::create($request->only('kode', 'description'));

        return response()->json([
            'success' => true,
            'message' => '✅ Kode berhasil ditambahkan.',
            'data'    => $kode,
        ]);
    }

    /**
     * Update data kode (TRIGGER auto update surat via event di model Kode)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $kode = Kode::findOrFail($id);

        $messages = [
            'kode.required' => 'Kolom Kode wajib diisi.',
            'kode.max'      => 'Kolom Kode maksimal 10 karakter.',
        ];

        // Validasi dasar
        $request->validate([
            'kode'        => 'required|max:10',
            'description' => 'nullable|string|max:255',
        ], $messages);

        // Cek duplikat manual, abaikan id yang sedang diedit
        $exists = Kode::where('kode', $request->kode)
            ->where('id_kode', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => [
                    'kode' => ['Kode telah tersedia.'],
                ],
            ], 422);
        }

        // Penting: pakai Eloquent → akan memicu event updating() di model Kode
        $kode->update($request->only('kode', 'description'));

        return response()->json([
            'success' => true,
            'message' => '✅ Kode berhasil diperbarui (surat terkait ikut disesuaikan).',
        ]);
    }

    /**
     * Hapus kode
     * Catatan:
     * - Jika di model Kode event deleting() DIUNCOMMENT,
     *   maka semua surat yang memakai kode ini akan di-null-kan kode_surat-nya.
     */
    public function destroy($id): JsonResponse
    {
        $kode = Kode::findOrFail($id);

        $kode->delete(); // akan memicu event deleting() kalau diaktifkan di model

        return response()->json([
            'success' => true,
            'message' => '🗑️ Kode berhasil dihapus.',
        ]);
    }
}
