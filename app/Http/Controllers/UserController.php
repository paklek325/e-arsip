<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


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
            ->when($ignoreUserId, fn ($q) => $q->where('id_user', '!=', $ignoreUserId))
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
            $query->where('name', 'like', "%{$search}%");
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
                ? asset('assets/foto_admin/' . $user->foto)
                : asset('assets/img/default_staf.png'),
            'role'     => [
                'name' => $user->role->name ?? '-',
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
            $filename = time() . '-' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

            $destination = public_path('assets/foto_admin');
            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);
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
        if ($request->hasFile('foto')) {
            $destination = public_path('assets/foto_admin');

            // hapus foto lama kalau ada
            if ($user->foto) {
                $oldPath = $destination . DIRECTORY_SEPARATOR . $user->foto;
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('foto');
            $filename = time() . '-' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);
            $data['foto'] = $filename;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
        ]);
    }

    // =========================
    // DESTROY (HAPUS USER)
    // =========================
    public function destroy(User $user)
    {
        // hapus file foto dari assets/foto_admin kalau ada
        if ($user->foto) {
            $path = public_path('assets/foto_admin/' . $user->foto);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus',
        ]);
    }
}