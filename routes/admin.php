<?php

use Illuminate\Support\Facades\Route;
use Rjcodes\Rjcms\Http\Controllers\Admin\AccountController;
use Rjcodes\Rjcms\Http\Controllers\Admin\BlogController;
use Rjcodes\Rjcms\Http\Controllers\Admin\BreadBuilderController;
use Rjcodes\Rjcms\Http\Controllers\Admin\BreadController;
use Rjcodes\Rjcms\Http\Controllers\Admin\CategoryController;
use Rjcodes\Rjcms\Http\Controllers\Admin\CustomFieldController;
use Rjcodes\Rjcms\Http\Controllers\Admin\DashboardController;
use Rjcodes\Rjcms\Http\Controllers\Admin\EditorController;
use Rjcodes\Rjcms\Http\Controllers\Admin\MediaController;
use Rjcodes\Rjcms\Http\Controllers\Admin\MenuController;
use Rjcodes\Rjcms\Http\Controllers\Admin\PermissionController;
use Rjcodes\Rjcms\Http\Controllers\Admin\RoleController;
use Rjcodes\Rjcms\Http\Controllers\Admin\SettingController;
use Rjcodes\Rjcms\Http\Controllers\Admin\UserController;
use Rjcodes\Rjcms\Http\Controllers\Auth\LoginController;
use Rjcodes\Rjcms\Http\Middleware\Authenticate;
use Rjcodes\Rjcms\Http\Middleware\RedirectIfAuthenticated;

Route::prefix(config('rjcms.prefix', 'admin'))->name('admin.')->group(function () {
    Route::middleware(RedirectIfAuthenticated::class)->group(function () {
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(Authenticate::class.':'.config('rjcms.guard', 'rjcms'))->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::group([], function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            // The signed-in admin's own account (username / email / password).
            Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
            Route::put('account', [AccountController::class, 'update'])->name('account.update');

            // Inline image uploads from the rich-text editor.
            Route::post('editor/upload', [EditorController::class, 'upload'])->name('editor.upload');
            // Built-in publishable content type (fixed fields, not BREAD-driven).
            // Site-wide blog template (registered before the resource so the
            // fixed "template" segment isn't matched as a {blog} record).
            Route::get('blogs/template', [BlogController::class, 'editTemplate'])->name('blogs.template.edit');
            Route::put('blogs/template', [BlogController::class, 'updateTemplate'])->name('blogs.template.update');
            Route::resource('blogs', BlogController::class)->except('show');
            Route::resource('categories', CategoryController::class)->except('show');

            // Typed custom fields attached to Blogs (stored in meta JSON).
            Route::prefix('content-fields/{group}')->name('content-fields.')->whereIn('group', ['blogs'])->group(function () {
                Route::get('/', [CustomFieldController::class, 'index'])->name('index');
                Route::get('create', [CustomFieldController::class, 'create'])->name('create');
                Route::post('/', [CustomFieldController::class, 'store'])->name('store');
                Route::get('{field}/edit', [CustomFieldController::class, 'edit'])->name('edit');
                Route::put('{field}', [CustomFieldController::class, 'update'])->name('update');
                Route::delete('{field}', [CustomFieldController::class, 'destroy'])->name('destroy');
            });

            Route::resource('users', UserController::class);
            Route::resource('roles', RoleController::class);
            Route::resource('permissions', PermissionController::class);
            Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
            Route::resource('media', MediaController::class);

            // Site settings — Voyager-style typed, grouped key/value pairs.
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [SettingController::class, 'index'])->name('index');
                Route::put('/', [SettingController::class, 'save'])->name('save');
                Route::get('create', [SettingController::class, 'create'])->name('create');
                Route::post('/', [SettingController::class, 'store'])->name('store');
                Route::get('{setting}/edit', [SettingController::class, 'edit'])->name('edit');
                Route::put('{setting}', [SettingController::class, 'update'])->name('update');
                Route::delete('{setting}', [SettingController::class, 'destroy'])->name('destroy');
            });

            // Navigation menu builder.
            Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
            Route::get('menus/create', [MenuController::class, 'create'])->name('menus.create');
            Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
            Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
            Route::put('menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
            Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');

            // Visual BREAD builder — manage BREAD definitions and their fields.
            Route::get('breads', [BreadBuilderController::class, 'index'])->name('breads.index');
            Route::get('breads/create', [BreadBuilderController::class, 'create'])->name('breads.create');
            Route::post('breads', [BreadBuilderController::class, 'store'])->name('breads.store');
            Route::get('breads/{bread}/edit', [BreadBuilderController::class, 'edit'])->name('breads.edit');
            Route::put('breads/{bread}', [BreadBuilderController::class, 'update'])->name('breads.update');
            Route::delete('breads/{bread}', [BreadBuilderController::class, 'destroy'])->name('breads.destroy');

            // Dynamic BREAD-managed content types.
            Route::prefix('content/{bread}')->name('bread.')->group(function () {
                Route::get('/', [BreadController::class, 'index'])->name('index');
                Route::get('create', [BreadController::class, 'create'])->name('create');
                Route::post('/', [BreadController::class, 'store'])->name('store');
                Route::get('{record}', [BreadController::class, 'show'])->name('show');
                Route::get('{record}/edit', [BreadController::class, 'edit'])->name('edit');
                Route::put('{record}', [BreadController::class, 'update'])->name('update');
                Route::delete('{record}', [BreadController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
