<?php

use Illuminate\Support\Facades\Schema;
use Rjcodes\Rjcms\Models\Category;
use Rjcodes\Rjcms\Models\Media;
use Rjcodes\Rjcms\Models\Setting;

it('adds an avatar column to the users table', function () {
    expect(Schema::hasColumn('users', 'avatar'))->toBeTrue();
});

it('respects the perPage query parameter on a listing', function () {
    $admin = superAdmin();
    Category::factory()->count(20)->create();

    $response = $this->actingAs($admin, 'rjcms')->get('/admin/categories?perPage=8');

    $response->assertOk();
    expect($response->viewData('categories')->perPage())->toBe(8);
});

it('lets an admin set a profile photo from the media library', function () {
    $admin = superAdmin();
    $media = Media::factory()->create();

    expect($admin->avatarUrl())->toBeNull();

    $this->actingAs($admin, 'rjcms')->put('/admin/account', [
        'name' => $admin->name,
        'email' => $admin->email,
        'avatar' => $media->id,
    ])->assertRedirect(route('admin.account.edit'));

    $admin->refresh();

    expect($admin->avatar)->toBe($media->id)
        ->and($admin->avatarUrl())->not->toBeNull();
});

it('lets an admin clear their profile photo', function () {
    $admin = superAdmin();
    $media = Media::factory()->create();
    $admin->forceFill(['avatar' => $media->id])->save();

    $this->actingAs($admin, 'rjcms')->put('/admin/account', [
        'name' => $admin->name,
        'email' => $admin->email,
        'avatar' => '',
    ])->assertRedirect();

    expect($admin->refresh()->avatar)->toBeNull();
});

it('rejects an avatar id that is not a real media record', function () {
    $admin = superAdmin();

    $this->actingAs($admin, 'rjcms')->put('/admin/account', [
        'name' => $admin->name,
        'email' => $admin->email,
        'avatar' => 999999,
    ])->assertSessionHasErrors('avatar');
});

it('refuses to delete a locked setting (site.name / site.logo)', function () {
    $admin = superAdmin(); // SettingSeeder already creates site.name and site.logo
    $name = Setting::where('key', 'site.name')->firstOrFail();
    $logo = Setting::where('key', 'site.logo')->firstOrFail();

    $this->actingAs($admin, 'rjcms')->delete(route('admin.settings.destroy', $name))->assertRedirect();
    $this->actingAs($admin, 'rjcms')->delete(route('admin.settings.destroy', $logo))->assertRedirect();

    expect(Setting::whereIn('key', ['site.name', 'site.logo'])->count())->toBe(2)
        ->and($name->isLocked())->toBeTrue();
});

it('still allows deleting an ordinary setting', function () {
    $admin = superAdmin();
    $custom = Setting::create(['group' => 'General', 'key' => 'general.custom', 'display_name' => 'Custom', 'type' => 'text']);

    $this->actingAs($admin, 'rjcms')->delete(route('admin.settings.destroy', $custom))->assertRedirect();

    expect(Setting::where('key', 'general.custom')->exists())->toBeFalse();
});
