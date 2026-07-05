<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('role.index', compact('roles'));
    }

    public function create()
    {
        return view('role.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        Role::create($validated);
        return redirect()->route('role.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function show(Role $role)
    {
        return view('role.show', compact('role'));
    }

    public function edit(Role $role)
    {
        return view('role.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name'        => 'required|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        $role->update($validated);
        return redirect()->route('role.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        try {
            $role->delete();
            return redirect()->route('role.index')->with('success', 'Role berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('role.index')->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user.');
        }
    }
}
