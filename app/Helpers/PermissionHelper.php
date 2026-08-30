<?php

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('can_do')) {
    /**
     * Helper untuk mengecek izin aksi user terhadap suatu menu
     *
     * @param string|int $menuIdentifier (code menu, route_name, atau menu_id)
     * @param string $action ('view', 'create', 'update', 'delete', 'export')
     * @param User|null $user
     * @return bool
     */
    function can_do(string|int $menuIdentifier, string $action = 'view', ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        return $user->canAccessMenu($menuIdentifier, $action);
    }
}

if (!function_exists('get_user_menus')) {
    /**
     * Mengambil daftar menu aktif yang diizinkan (can_view = true) untuk user yang sedang login
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function get_user_menus()
    {
        $user = Auth::user();

        if (!$user) {
            return collect();
        }

        // Ambil menu level utama (parent_id is null) yang aktif
        $menus = Menu::with(['children' => function ($query) use ($user) {
            $query->where('is_active', true)->orderBy('order_index', 'asc');
        }])
        ->whereNull('parent_id')
        ->where('is_active', true)
        ->orderBy('order_index', 'asc')
        ->get();

        // Jika superadmin, return semua menu aktif
        if ($user->isSuperAdmin()) {
            return $menus;
        }

        // Filter menu berdasarkan permission can_view
        return $menus->filter(function ($menu) use ($user) {
            $hasAccessParent = $user->canAccessMenu($menu->id, 'view');
            
            // Filter children yang diizinkan
            $allowedChildren = $menu->children->filter(function ($child) use ($user) {
                return $user->canAccessMenu($child->id, 'view');
            });

            // Set filtered children ke instance menu
            $menu->setRelation('children', $allowedChildren);

            // Menu tampil jika user punya akses ke menu itu sendiri ATAU memiliki anak menu yang diizinkan
            return $hasAccessParent || $allowedChildren->isNotEmpty();
        });
    }
}

if (!function_exists('get_unconfigured_schedules_count')) {
    /**
     * Menghitung jumlah pegawai aktif yang belum diatur jam kerjanya (untuk notifikasi badge HRD)
     * Pegawai dianggap belum diatur jika tidak memiliki sesi mengajar dan tidak memiliki jadwal hari aktif kerja
     *
     * @return int
     */
    function get_unconfigured_schedules_count(): int
    {
        try {
            return User::where('is_active', true)
                ->whereDoesntHave('teachingSlots')
                ->whereDoesntHave('workSchedules', function ($q) {
                    $q->where('is_day_off', false)->whereNotNull('time_in');
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
