<?php

use Illuminate\Support\Facades\Route;
use Rjcodes\Rjcms\Http\Controllers\BlogController;
use Rjcodes\Rjcms\Http\Controllers\ContactController;

// Blog front-end.
Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

/*
|--------------------------------------------------------------------------
| Static pages (Blade files)
|--------------------------------------------------------------------------
| Pages are plain Blade views under resources/views/pages/. Register a route
| for each one here, pointing at its view. Use Route::view() for a static
| page, or a controller when the page needs to handle input (see Contact).
|
|   Route::view('/about', 'rjcms::pages.about')->name('pages.about');
*/

Route::view('/contact', 'rjcms::pages.contact')->name('pages.contact');
Route::post('contact', [ContactController::class, 'send'])->name('contact.send');
