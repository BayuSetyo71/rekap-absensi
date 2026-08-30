<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // Directive kustom: @canDo('menu_code', 'action')
        Blade::if('canDo', function ($menuIdentifier, $action = 'view') {
            return function_exists('can_do') && can_do($menuIdentifier, $action);
        });

        // Directive kustom: @canView('menu_code')
        Blade::if('canView', function ($menuIdentifier) {
            return function_exists('can_do') && can_do($menuIdentifier, 'view');
        });

        // Directive kustom: @canCreate('menu_code')
        Blade::if('canCreate', function ($menuIdentifier) {
            return function_exists('can_do') && can_do($menuIdentifier, 'create');
        });

        // Directive kustom: @canUpdate('menu_code')
        Blade::if('canUpdate', function ($menuIdentifier) {
            return function_exists('can_do') && can_do($menuIdentifier, 'update');
        });

        // Directive kustom: @canDelete('menu_code')
        Blade::if('canDelete', function ($menuIdentifier) {
            return function_exists('can_do') && can_do($menuIdentifier, 'delete');
        });

        // Directive kustom: @canExport('menu_code')
        Blade::if('canExport', function ($menuIdentifier) {
            return function_exists('can_do') && can_do($menuIdentifier, 'export');
        });
    }
}
