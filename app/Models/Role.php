<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system',
    ];

    /**
     * Relasi ke users yang memiliki role ini
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke hak akses menu per role
     */
    public function menuPermissions(): HasMany
    {
        return $this->hasMany(RoleMenuPermission::class);
    }

    /**
     * Mengecek apakah role ini memiliki izin tertentu pada sebuah menu
     *
     * @param string|int $menuIdentifier (code, route_name, atau menu_id)
     * @param string $action ('view', 'create', 'update', 'delete', 'export')
     * @return bool
     */
    public function hasPermission(string|int $menuIdentifier, string $action = 'view'): bool
    {
        // Superadmin selalu memiliki akses penuh
        if ($this->name === 'superadmin') {
            return true;
        }

        $column = 'can_' . strtolower($action);

        $permission = $this->menuPermissions()
            ->whereHas('menu', function ($query) use ($menuIdentifier) {
                if (is_numeric($menuIdentifier)) {
                    $query->where('id', $menuIdentifier);
                } else {
                    $query->where('code', $menuIdentifier)
                          ->orWhere('route_name', $menuIdentifier);
                }
            })
            ->first();

        return $permission ? (bool) $permission->{$column} : false;
    }
}
