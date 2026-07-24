<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class UserController extends Controller
{
    /**
     * Ambil id_role Kepala Staf (role yang hanya boleh dimiliki satu user).
     */
    private function kepalaStafRoleId(): ?int
    {
        return Role::where('name', 'Kepala Staf')->value('id_role');
    }

    /**
     * Pastikan tidak ada lebih dari satu Kepala Staf.
     * Mengembalikan JsonResponse error bila melanggar, atau null bila lolos.
     *
     * @param  int|null  $ignoreUserId  id_user yang diabaikan (untuk update)
     */
    private function guardSingleKepalaStaf(mixed $requestRoleId, ?int $ignoreUserId = null)
    {
        $kepalaRoleId = $this->kepalaStafRoleId();

        if (!$kepalaRoleId || (int) $requestRoleId !== (int) $kepalaRoleId) {
            return null; // bukan Kepala Staf, tidak perlu dibatasi
        }

        $exists = User::where('id_role', $kepalaRoleId)
            ->when($ignoreUserId, fn($q) => $q->where('id_user', '!=', $ignoreUserId))
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => [
                    'id_role' => ['Kepala Staf sudah ada. Aplikasi hanya boleh memiliki satu Kepala Staf.'],
                ],
            ], 422);
        }

        return null;
    }

    // =========================
    // INDEX + SEARCH + AJAX TABLE
    // =========================
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Prioritas urutan: Kepala Staf selalu di paling atas,
        // baru diikuti urutan normal (nama A-Z).
        $kepalaRoleId = $this->kepalaStafRoleId();

        $users = $query
            ->orderByRaw('CASE WHEN id_role = ? THEN 0 ELSE 1 END', [$kepalaRoleId])
            ->orderBy('name', 'asc')
            ->paginate(10);
        $roles = Role::orderBy('name')->get();

        if ($request->ajax()) {
            return view('user.partials.table', compact('users'))->render();
        }

        return view('user.index', compact('users', 'roles'));
    }

    // =========================
    // SHOW (DETAIL / EDIT)
    // =========================
    public function show(User $user)
    {
        $user->load('role');

        return response()->json([
            'success'  => true,
            'id_user'  => $user->id_user,
            'name'     => $user->name,
            'email'    => $user->email,
            'id_role'  => $user->id_role,
            'foto'     => $user->foto
                ? asset('storage/foto_admin/' . $user->foto)
                : asset('assets/img/default_staf.png'),
            'role'     => [
                'name' => $user->role?->name ?? '-',
            ],
        ]);
    }

    // =========================
    // STORE (TAMBAH USER)
    // =========================
    public function store(Request $request)
    {
        // Log::info('Request data (Store):', $request->all()); // Debugging: cek data input

        $validator = Validator::make(
            $request->all(),
            [
                'name'     => 'required|max:150',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|min:3',
                'id_role'  => 'required|exists:roles,id_role',
                'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            ],
            [
                'email.unique' => 'Email sudah digunakan.',
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();

            // Logika untuk menangani kasus duplikasi Email + Password
            if ($errors->has('email') && $request->filled('password')) {
                $existing = User::where('email', $request->email)->first();

                if ($existing && Hash::check($request->password, $existing->password)) {
                    // Timpa pesan error standar dengan pesan duplikasi khusus
                    $errors->add('email', 'Email dan password telah digunakan.');
                    $errors->add('password', 'Email dan password telah digunakan.');
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $errors,
            ], 422);
        }

        // Batasi hanya boleh ada satu Kepala Staf
        if ($guard = $this->guardSingleKepalaStaf($request->id_role)) {
            return $guard;
        }

        $validated = $validator->validated();

        // --- Proses Upload Foto ---
        $validated['foto'] = null;

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('foto_admin', $filename, 'public');
            $validated['foto'] = $filename;
        }

        // hash password
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data'    => $user,
        ]);
    }

    // =========================
    // UPDATE (EDIT USER)
    // =========================
    public function update(Request $request, User $user)
    {
        // Log::info('Request data (Update):', $request->all()); // Debugging: cek data input

        $kepalaRoleId = $this->kepalaStafRoleId();
        $isKepalaStaf = $kepalaRoleId && (int) $user->id_role === (int) $kepalaRoleId;

        $validator = Validator::make(
            $request->all(),
            [
                'name'     => 'required|max:150',
                // Kepala Staf: email & role tidak dikirim dari form (field disabled/readonly)
                'email'    => $isKepalaStaf ? 'nullable' : 'required|email|unique:users,email,' . $user->id_user . ',id_user',
                'password' => 'nullable|min:3',
                'id_role'  => $isKepalaStaf ? 'nullable' : 'required|exists:roles,id_role',
                'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            ],
            [
                'email.unique' => 'Email sudah digunakan.',
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();

            // Logika untuk menangani kasus duplikasi Email + Password
            if ($errors->has('email') && $request->filled('password')) {
                $existing = User::where('email', $request->email)
                    ->where('id_user', '!=', $user->id_user)
                    ->first();

                if ($existing && Hash::check($request->password, $existing->password)) {
                    // Timpa pesan error standar dengan pesan duplikasi khusus
                    $errors->add('email', 'Email dan password telah digunakan.');
                    $errors->add('password', 'Email dan password telah digunakan.');
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $errors,
            ], 422);
        }

        // Batasi hanya boleh ada satu Kepala Staf — skip jika user ini memang sudah Kepala Staf
        if (!$isKepalaStaf && ($guard = $this->guardSingleKepalaStaf($request->id_role, $user->id_user))) {
            return $guard;
        }

        $validated = $validator->validated();

        $kepalaRoleId  = $this->kepalaStafRoleId();
        $isKepalaStaf  = $kepalaRoleId && (int) $user->id_role === (int) $kepalaRoleId;

        $data = [
            'name'    => $validated['name'],
            // Kepala Staf: email & role tidak boleh diubah
            'email'   => $isKepalaStaf ? $user->email   : $validated['email'],
            'id_role' => $isKepalaStaf ? $user->id_role : $validated['id_role'],
        ];

        // update password bila diisi
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // --- Proses Upload Foto Baru ---
        // URUTAN AMAN: upload foto baru dulu → update DB → hapus foto lama.
        // Sebelumnya: hapus foto lama dulu → jika update DB gagal, foto hilang
        // permanen (data orphan). Sekarang foto lama hanya dihapus setelah DB
        // berhasil di-update, sehingga rollback tetap bisa mempertahankan foto.
        $fotoLamaUntukDihapus = null;

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('foto_admin', $filename, 'public');
            $data['foto'] = $filename;

            // Tandai foto lama untuk dihapus setelah DB update berhasil
            if ($user->foto) {
                $fotoLamaUntukDihapus = $user->foto;
            }
        }

        $user->update($data);

        // Hapus foto lama HANYA setelah DB update berhasil
        if ($fotoLamaUntukDihapus) {
            Storage::disk('public')->delete('foto_admin/' . $fotoLamaUntukDihapus);
        }

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
        ]);
    }

    // =========================
    // PROFIL — halaman profil user yang sedang login
    // =========================
    public function profil()
    {
        $user = auth()->user()->load('role');
        return view('user.profil', compact('user'));
    }

    public function updateProfil(Request $request)
    {
        $user         = auth()->user()->load('role');
        $isAjax       = $request->ajax() || $request->wantsJson();
        $isKepalaStaf = strtolower($user->role->name ?? '') === 'kepala staf';

        $validator = Validator::make($request->all(), [
            'name'     => 'required|max:150',
            'email'    => 'required|email|unique:users,email,' . $user->id_user . ',id_user',
            'password' => 'nullable|min:3',
            'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ], [
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
        ]);

        if ($validator->fails()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        // Kepala Staf tidak boleh mengubah email sendiri. Field di form sudah
        // dibuat readonly, tapi validasi di server tetap diperlukan supaya
        // permintaan yang dikirim langsung (bypass UI) juga ditolak.
        if ($isKepalaStaf && $request->email !== $user->email) {
            $errors = ['email' => ['Email Kepala Staf tidak dapat diubah.']];

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $errors,
                ], 422);
            }

            return back()->withErrors($errors)->withInput();
        }

        $data = [
            'name'  => $request->name,
            'email' => $isKepalaStaf ? $user->email : $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete('foto_admin/' . $user->foto);
            }
            $file     = $request->file('foto');
            $filename = time() . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('foto_admin', $filename, 'public');
            $data['foto'] = $filename;
        }

        $user->update($data);
        $user->refresh()->load('role');

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui.',
                'user'    => [
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'foto'      => $user->foto,
                    'foto_url'  => $user->foto
                        ? asset('storage/foto_admin/' . $user->foto)
                        : asset('assets/img/default_staf.png'),
                    'role_name' => $user->role->name ?? 'User',
                ],
            ]);
        }

        return redirect()->route('dashboard.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    // =========================
    // DESTROY (HAPUS USER)
    // =========================
    public function destroy(User $user)
    {
        // Kepala Staf tidak boleh dihapus dari sistem (hanya boleh diedit),
        // supaya aplikasi tidak pernah kehilangan akun Kepala Staf.
        $kepalaRoleId = $this->kepalaStafRoleId();
        if ($kepalaRoleId && (int) $user->id_role === (int) $kepalaRoleId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Kepala Staf tidak dapat dihapus.',
            ], 422);
        }

        try {
            $foto = $user->foto;

            $user->delete();

            if ($foto) {
                Storage::disk('public')->delete('foto_admin/' . $foto);
            }

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage(),
            ], 500);
        }
    }
}