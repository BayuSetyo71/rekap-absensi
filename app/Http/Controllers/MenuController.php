<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    /**
     * Menampilkan daftar semua menu
     */
    public function index()
    {
        $menus = Menu::with(['parent', 'children'])
            ->whereNull('parent_id')
            ->orderBy('order_index', 'asc')
            ->get();

        $allMenus = Menu::orderBy('name', 'asc')->get();
        $parentMenus = Menu::whereNull('parent_id')->orderBy('name', 'asc')->get();

        return view('menus.index', compact('menus', 'allMenus', 'parentMenus'));
    }

    /**
     * Menyimpan menu baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50', 'unique:menus,code'],
            'route_name' => ['nullable', 'string', 'max:100'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:menus,id'],
            'order_index' => ['nullable', 'integer'],
        ], [
            'name.required' => 'Nama menu wajib diisi.',
            'code.unique' => 'Kode menu sudah digunakan.',
        ]);

        $code = $request->filled('code')
            ? Str::slug($request->input('code'), '_')
            : Str::slug($request->input('name'), '_');

        // Pastikan unique code
        $count = Menu::where('code', $code)->count();
        if ($count > 0) {
            $code .= '_' . ($count + 1);
        }

        $menu = Menu::create([
            'parent_id' => $request->input('parent_id') ?: null,
            'code' => $code,
            'name' => $request->input('name'),
            'route_name' => $request->input('route_name'),
            'url' => $request->input('url'),
            'icon' => $request->input('icon', 'bi bi-circle'),
            'order_index' => $request->input('order_index', 0) ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'has_create' => $request->boolean('has_create', true),
            'has_update' => $request->boolean('has_update', true),
            'has_delete' => $request->boolean('has_delete', true),
            'has_export' => $request->boolean('has_export', false),
        ]);

        // Buat permission default untuk semua role yang ada
        $roles = Role::all();
        foreach ($roles as $role) {
            $isSuperAdminOrAdmin = in_array($role->name, ['superadmin', 'admin']);
            RoleMenuPermission::create([
                'role_id' => $role->id,
                'menu_id' => $menu->id,
                'can_view' => $isSuperAdminOrAdmin,
                'can_create' => $isSuperAdminOrAdmin && $menu->has_create,
                'can_update' => $isSuperAdminOrAdmin && $menu->has_update,
                'can_delete' => $isSuperAdminOrAdmin && $menu->has_delete,
                'can_export' => $isSuperAdminOrAdmin && $menu->has_export,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Menu baru berhasil ditambahkan.',
                'data' => $menu,
            ]);
        }

        return redirect()->route('menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    /**
     * Mengambil detail menu untuk modal edit (JSON via jQuery AJAX)
     */
    public function edit(Menu $menu)
    {
        return response()->json([
            'status' => 'success',
            'data' => $menu,
        ]);
    }

    /**
     * Memperbarui data menu
     */
    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:menus,code,' . $menu->id],
            'route_name' => ['nullable', 'string', 'max:100'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:menus,id'],
            'order_index' => ['nullable', 'integer'],
        ], [
            'name.required' => 'Nama menu wajib diisi.',
            'code.required' => 'Kode menu wajib diisi.',
            'code.unique' => 'Kode menu sudah digunakan.',
        ]);

        // Mencegah menu menjadi parent bagi dirinya sendiri
        $parentId = $request->input('parent_id') ?: null;
        if ($parentId == $menu->id) {
            $parentId = null;
        }

        $menu->update([
            'parent_id' => $parentId,
            'code' => Str::slug($request->input('code'), '_'),
            'name' => $request->input('name'),
            'route_name' => $request->input('route_name'),
            'url' => $request->input('url'),
            'icon' => $request->input('icon', 'bi bi-circle'),
            'order_index' => $request->input('order_index', 0) ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'has_create' => $request->boolean('has_create', false),
            'has_update' => $request->boolean('has_update', false),
            'has_delete' => $request->boolean('has_delete', false),
            'has_export' => $request->boolean('has_export', false),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Menu berhasil diperbarui.',
                'data' => $menu,
            ]);
        }

        return redirect()->route('menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    /**
     * Menghapus menu
     */
    public function destroy(Request $request, Menu $menu)
    {
        $menu->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Menu berhasil dihapus.',
            ]);
        }

        return redirect()->route('menus.index')->with('success', 'Menu berhasil dihapus.');
    }

    /**
     * Toggle status aktif menu via jQuery AJAX
     */
    public function toggleActive(Request $request, Menu $menu)
    {
        $menu->is_active = !$menu->is_active;
        $menu->save();

        return response()->json([
            'status' => 'success',
            'is_active' => $menu->is_active,
            'message' => 'Status menu ' . $menu->name . ' berhasil diubah.',
        ]);
    }
}
