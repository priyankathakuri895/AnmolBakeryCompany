<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RawMaterialController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierVehicleController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/products', function () {
    return view('products');
})->name('products');

Route::get('/gallery', function () {
    return view('gallery');
})->name('gallery');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

/*
|--------------------------------------------------------------------------
| Owner login
|--------------------------------------------------------------------------
| There is no registration route. The owner account is created by the
| seeder, so nobody can sign themselves up.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin dashboard (owner only)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', DashboardController::class)->name('dashboard');

        // Van management
        Route::resource('suppliers', SupplierController::class)->except('show');
        Route::resource('vehicles', SupplierVehicleController::class)
            ->parameters(['vehicles' => 'vehicle'])
            ->except('show');

        // Raw material stock management
        Route::resource('materials', RawMaterialController::class)
            ->parameters(['materials' => 'material'])
            ->except('show');
    });

/*
| A friendly /dashboard URL, and it makes Laravel send an already
| logged-in owner to the admin panel instead of the public site.
*/
Route::redirect('/dashboard', '/admin')->name('dashboard');
