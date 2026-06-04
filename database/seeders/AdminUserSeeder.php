<?php

namespace Rjcodes\Rjcms\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Rjcodes\Rjcms\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default super admin account.
     *
     * Idempotent: matches on email, so re-running never duplicates the admin.
     * Credentials and the user model are driven by config so a host app can
     * point the CMS at its own User model and seed its own admin.
     */
    public function run(): void
    {
        /** @var class-string<Model> $model */
        $model = config('rjcms.user_model', User::class);

        $admin = $model::updateOrCreate(
            ['email' => config('rjcms.admin.email', 'admin@admin.com')],
            [
                'name' => config('rjcms.admin.name', 'Super Admin'),
                'password' => Hash::make(config('rjcms.admin.password', 'password')),
                'email_verified_at' => now(),
            ],
        );

        $admin->syncRoles(config('rjcms.super_admin_role', 'super_admin'));
    }
}
