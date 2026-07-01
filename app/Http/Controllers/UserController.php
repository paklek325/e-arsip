<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Ditambahkan untuk debugging (opsional)

class UserController extends Controller
{
    // =========================
    // INDEX + SEARCH + AJAX TABLE
    // =========================
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $users = $query->orderBy('name', 'asc')->paginate(10);
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
            // kalau foto null → pakai noimage dari assets/img
            'foto'     => $user->foto
                ? asset('assets/foto_admin/' . $user->foto)
                : asset('assets/img/noimage.png'),
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
                'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
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

        $validator = Validator::make(
            $request->all(),
            [
                'name'     => 'required|max:150',
                // Abaikan email user saat ini dari pengecekan unique
                'email'    => 'required|email|unique:users,email,' . $user->id_user . ',id_user',
                'password' => 'nullable|min:3', // Opsional
                'id_role'  => 'required|exists:roles,id_role',
                'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
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

        $validated = $validator->validated();

        $data = [
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'id_role' => $validated['id_role'],
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