<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Menampilkan daftar semua role
     */
    public function index()
    {
        $roles = Role::withCount(['users', 'menuPermissions'])->get();

        return view('roles.index', compact('roles'));
    }

    /**
     * Menyimpan role baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:50', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'display_name.required' => 'Nama tampilan role wajib diisi.',
            'name.unique' => 'Slug/Kode role sudah digunakan.',
        ]);

        $name = $request->filled('name')
            ? Str::slug($request->input('name'))
            : Str::slug($request->input('display_name'));

        // Pastikan unique slug
        $count = Role::where('name', $name)->count();
        if ($count > 0) {
            $name .= '-' . ($count + 1);
        }

        $role = Role::create([
            'name' => $name,
            'display_name' => $request->input('display_name'),
            'description' => $request->input('description'),
            'is_system' => false,
        ]);

        // Inisialisasi permission kosong untuk semua menu
        $menus = Menu::all();
        foreach ($menus as $menu) {
            RoleMenuPermission::create([
                'role_id' => $role->id,
                'menu_id' => $menu->id,
                'can_view' => false,
                'can_create' => false,
                'can_update' => false,
                'can_delete' => false,
                'can_export' => false,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Role berhasil ditambahkan.',
                'data' => $role,
            ]);
        }

        return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan.');
    }

    /**
     * Menampilkan data role untuk modal edit (JSON via jQuery AJAX)
     */
    public function edit(Role $role)
    {
        return response()->json([
            'status' => 'success',
            'data' => $role,
        ]);
    }

    /**
     * Memperbarui data role
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'display_name.required' => 'Nama tampilan role wajib diisi.',
        ]);

        $role->update([
            'display_name' => $request->input('display_name'),
            'description' => $request->input('description'),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Role berhasil diperbarui.',
                'data' => $role,
            ]);
        }

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    /**
     * Menghapus role
     */
    public function destroy(Request $request, Role $role)
    {
        if ($role->is_system) {
            $msg = 'Role bawaan sistem tidak dapat dihapus.';
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return redirect()->route('roles.index')->with('error', $msg);
        }

        if ($role->users()->count() > 0) {
            $msg = 'Role ini masih digunakan oleh ' . $role->users()->count() . ' pengguna. Pindahkan pengguna terlebih dahulu.';
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return redirect()->route('roles.index')->with('error', $msg);
        }

        $role->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Role berhasil dihapus.',
            ]);
        }

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }

    /**
     * Menampilkan halaman Matriks Izin Menu per Role
     */
    public function permissions(Role $role)
    {
        // Ambil semua menu utama beserta sub-menu
        $menus = Menu::with('children')->whereNull('parent_id')->orderBy('order_index', 'asc')->get();

        // Ambil permissions yang sudah ada untuk role ini dipetakan berdasarkan menu_id
        $permissions = RoleMenuPermission::where('role_id', $role->id)
            ->get()
            ->keyBy('menu_id');

        return view('roles.permissions', compact('role', 'menus', 'permissions'));
    }

    /**
     * Memperbarui matriks izin menu untuk sebuah role
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $permissionsInput = $request->input('permissions', []);

        $allMenus = Menu::all();

        foreach ($allMenus as $menu) {
            $menuPerms = $permissionsInput[$menu->id] ?? [];

            RoleMenuPermission::updateOrCreate(
                [
                    'role_id' => $role->id,
                    'menu_id' => $menu->id,
                ],
                [
                    'can_view'   => !empty($menuPerms['view']),
                    'can_create' => !empty($menuPerms['create']) && $menu->has_create,
                    'can_update' => !empty($menuPerms['update']) && $menu->has_update,
                    'can_delete' => !empty($menuPerms['delete']) && $menu->has_delete,
                    'can_export' => !empty($menuPerms['export']) && $menu->has_export,
                ]
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Hak akses izin menu untuk role ' . $role->display_name . ' berhasil disimpan.',
            ]);
        }

        return redirect()->route('roles.permissions', $role->id)
            ->with('success', 'Hak akses izin menu berhasil diperbarui.');
    }
}
