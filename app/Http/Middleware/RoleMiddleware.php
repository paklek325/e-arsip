<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Cara pakai di route (nama role mengikuti kolom `roles.name` di DB,
     * lihat database/seeders/RolesTableSeeder.php — saat ini: "Kepala Staf", "Staf"):
     *   ->middleware('role:Kepala Staf')
     *   ->middleware('role:Kepala Staf,Staf')
     *
     * Perbandingan tidak case-sensitive dan menerima underscore sebagai
     * pengganti spasi, jadi 'kepala_staf' atau 'KEPALA STAF' juga cocok.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // === Ambil role user ===
        $userRole = null;

        // Jika pakai relasi role (User -> Role)
        if (method_exists($user, 'role')) {
            $userRole = optional($user->role)->name;
        }

        // Jika tidak ada relasi, coba ambil langsung dari kolom `role`
        if (!$userRole && isset($user->role)) {
            $userRole = $user->role;
        }

        // === Validasi role (tidak case-sensitive, underscore = spasi) ===
        $normalize = fn(string $r) => strtolower(str_replace('_', ' ', trim($r)));

        $userRoleNormalized = $userRole ? $normalize($userRole) : null;
        $allowedNormalized  = array_map($normalize, $roles);

        if (!$userRoleNormalized || !in_array($userRoleNormalized, $allowedNormalized)) {
            abort(403, 'Akses ditolak!');
        }

        return $next($request);
    }
}
