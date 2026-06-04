<?php

namespace Rjcodes\Rjcms\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Rjcodes\Rjcms\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Resources that are managed through the CMS.
     *
     * @var list<string>
     */
    private const RESOURCES = ['users', 'roles', 'permissions', 'medias', 'breads', 'settings', 'blogs', 'categories', 'menus'];

    /**
     * Actions available for every managed resource.
     *
     * @var list<string>
     */
    private const ACTIONS = ['browse', 'view', 'create', 'edit', 'delete'];

    /**
     * Seed the application's roles and permissions.
     */
    public function run(): void
    {
        App::make(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::RESOURCES as $resource) {
            foreach (self::ACTIONS as $action) {
                Permission::findOrCreate("{$action}_{$resource}", 'web');
            }
        }

        $superAdmin = Role::findOrCreate(config('rjcms.super_admin_role', 'super_admin'), 'web');
        $superAdmin->syncPermissions(Permission::all());
    }
}
