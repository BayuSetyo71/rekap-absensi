<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'route_name',
        'url',
        'icon',
        'order_index',
        'is_active',
        'has_create',
        'has_update',
        'has_delete',
        'has_export',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_create' => 'boolean',
        'has_update' => 'boolean',
        'has_delete' => 'boolean',
        'has_export' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Relasi ke menu induk (parent)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Relasi ke sub menu (children)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order_index', 'asc');
    }

    /**
     * Relasi ke permissions yang terkait dengan menu ini
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(RoleMenuPermission::class, 'menu_id');
    }

    /**
     * Mendapatkan URL dari route_name atau fallback ke url langsung
     */
    public function getLinkAttribute(): string
    {
        if (!empty($this->route_name) && \Illuminate\Support\Facades\Route::has($this->route_name)) {
            return route($this->route_name);
        }

        if (!empty($this->url)) {
            return url($this->url);
        }

        return '#';
    }

    /**
     * Mengecek apakah route menu ini sedang aktif
     */
    public function isCurrentRoute(): bool
    {
        if (!empty($this->route_name) && request()->routeIs($this->route_name . '*')) {
            return true;
        }

        if (!empty($this->url) && request()->is(ltrim($this->url, '/') . '*')) {
            return true;
        }

        return false;
    }
}
