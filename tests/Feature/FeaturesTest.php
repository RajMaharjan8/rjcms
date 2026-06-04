<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Rjcodes\Rjcms\Models\Category;

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

it('lets an admin upload a profile photo', function () {
    Storage::fake('public');
    $admin = superAdmin();

    expect($admin->avatarUrl())->toBeNull();

    $this->actingAs($admin, 'rjcms')->put('/admin/account', [
        'name' => $admin->name,
        'email' => $admin->email,
        'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
    ])->assertRedirect(route('admin.account.edit'));

    $admin->refresh();

    expect($admin->avatar)->not->toBeNull()
        ->and($admin->avatarUrl())->not->toBeNull();
});

it('lets an admin remove their profile photo', function () {
    Storage::fake('public');
    $admin = superAdmin();

    $this->actingAs($admin, 'rjcms')->put('/admin/account', [
        'name' => $admin->name,
        'email' => $admin->email,
        'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
    ]);
    expect($admin->refresh()->avatar)->not->toBeNull();

    $this->actingAs($admin, 'rjcms')->put('/admin/account', [
        'name' => $admin->name,
        'email' => $admin->email,
        'remove_avatar' => '1',
    ]);

    expect($admin->refresh()->avatar)->toBeNull();
});
