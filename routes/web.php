<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\MaterialReceiptController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductionEntryController;
use App\Http\Controllers\Admin\ProductStockCheckController;
use App\Http\Controllers\Admin\RawMaterialController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SalesmanController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierVehicleController;
use App\Http\Controllers\Admin\VanController;
use App\Http\Controllers\Admin\VanDebitTransactionController;
use App\Http\Controllers\Admin\VanLoadController;
use App\Http\Controllers\Admin\VanSettlementController;
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

        // Purchasing
        Route::resource('suppliers', SupplierController::class);
        Route::resource('vehicles', SupplierVehicleController::class)
            ->parameters(['vehicles' => 'vehicle'])
            ->except('show');

        // Raw material stock management
        Route::resource('materials', RawMaterialController::class)
            ->parameters(['materials' => 'material'])
            ->except('show');

        /*
        | Receiving. Reading a bill photo is its own endpoint because it only
        | returns suggestions — it saves nothing until the form is submitted.
        */
        Route::post('receipts/read-bill', [MaterialReceiptController::class, 'readBill'])
            ->middleware('throttle:20,1')
            ->name('receipts.read-bill');

        Route::get('receipts', [MaterialReceiptController::class, 'index'])->name('receipts.index');
        Route::get('receipts/create', [MaterialReceiptController::class, 'create'])->name('receipts.create');
        Route::post('receipts', [MaterialReceiptController::class, 'store'])->name('receipts.store');
        Route::get('receipts/{receipt}', [MaterialReceiptController::class, 'show'])->name('receipts.show');

        Route::patch('receipts/{receipt}/bill-stacked', [MaterialReceiptController::class, 'stackBill'])
            ->name('receipts.stack-bill');

        // The later delivery that settles what was short.
        Route::get('receipts/{receipt}/follow-up', [MaterialReceiptController::class, 'createFollowUp'])
            ->name('receipts.follow-up.create');
        Route::post('receipts/{receipt}/follow-up', [MaterialReceiptController::class, 'storeFollowUp'])
            ->name('receipts.follow-up.store');

        // Finished-goods catalog
        Route::resource('products', ProductController::class)->except('show');

        // Finished-goods stock movements
        Route::get('production', [ProductionEntryController::class, 'index'])->name('production.index');
        Route::get('production/create', [ProductionEntryController::class, 'create'])->name('production.create');
        Route::post('production', [ProductionEntryController::class, 'store'])->name('production.store');

        Route::resource('product-stock-checks', ProductStockCheckController::class)
            ->parameters(['product-stock-checks' => 'stockCheck'])
            ->except('show');
        Route::post('product-stock-checks/{stockCheck}/finalize', [ProductStockCheckController::class, 'finalize'])
            ->name('product-stock-checks.finalize');

        // Sales fleet
        Route::resource('salesmen', SalesmanController::class)
            ->parameters(['salesmen' => 'salesman'])
            ->except('show');
        Route::resource('vans', VanController::class)
            ->parameters(['vans' => 'van'])
            ->except('show');

        // Daily van loading
        Route::resource('van-loads', VanLoadController::class)
            ->parameters(['van-loads' => 'vanLoad'])
            ->only(['index', 'create', 'store', 'show', 'destroy']);

        // Daily settlement (returns + collections) and the van debit ledger
        Route::resource('van-settlements', VanSettlementController::class)
            ->parameters(['van-settlements' => 'settlement'])
            ->only(['index', 'store', 'edit', 'update', 'destroy']);
        Route::post('van-settlements/{settlement}/post-returns', [VanSettlementController::class, 'postReturns'])
            ->name('van-settlements.post-returns');
        Route::post('van-settlements/{settlement}/finalize', [VanSettlementController::class, 'finalize'])
            ->name('van-settlements.finalize');

        Route::get('van-debit', [VanDebitTransactionController::class, 'index'])->name('van-debit.index');
        Route::get('van-debit/create', [VanDebitTransactionController::class, 'create'])->name('van-debit.create');
        Route::post('van-debit', [VanDebitTransactionController::class, 'store'])->name('van-debit.store');

        // Expenses
        Route::resource('expenses', ExpenseController::class)->except('show');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

/*
| A friendly /dashboard URL, and it makes Laravel send an already
| logged-in owner to the admin panel instead of the public site.
*/
Route::redirect('/dashboard', '/admin')->name('dashboard');
