<?php

namespace Rjcodes\Rjcms\Database\Seeders;

use Illuminate\Database\Seeder;
use Rjcodes\Rjcms\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Seed a handful of default site settings, grouped into categories.
     */
    public function run(): void
    {
        $settings = [
            ['group' => 'Site', 'key' => 'site.name', 'display_name' => 'Site name', 'type' => 'text', 'required' => true, 'order' => 1, 'value' => config('app.name')],
            ['group' => 'Site', 'key' => 'site.description', 'display_name' => 'Site description', 'type' => 'textarea', 'order' => 2],
            ['group' => 'Site', 'key' => 'site.logo', 'display_name' => 'Logo', 'type' => 'image', 'order' => 3],
            ['group' => 'General', 'key' => 'general.maintenance_mode', 'display_name' => 'Maintenance mode', 'type' => 'boolean', 'order' => 1],
            ['group' => 'Blog', 'key' => 'blog.post_template', 'display_name' => 'Default post template', 'type' => 'text', 'order' => 1],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
