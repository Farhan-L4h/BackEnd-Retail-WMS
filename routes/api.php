<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\LaporanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Hanya Bisa diakses Admin
    //KATEGORI
    Route::get('/kategori', [GudangController::class, 'indexKategori'])->name('kategori.index');
    Route::get('/kategori/{id}/show', [GudangController::class, 'showKategori'])->name('kategori.show');
    Route::post('/kategori', [GudangController::class, 'storeKategori'])->name('kategori.store');
    Route::put('/kategori/{id}/update', [GudangController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}/destroy', [GudangController::class, 'destroyKategori'])->name('kategori.destroy');

    //SUPPLIER
    Route::get('/supplier', [GudangController::class, 'indexSupplier'])->name('supplier.index');
    Route::get('/supplier/{id}/show', [GudangController::class, 'showSupplier'])->name('supplier.show');
    Route::post('/supplier', [GudangController::class, 'storeSupplier'])->name('supplier.store');
    Route::put('/supplier/{id}/update', [GudangController::class, 'updateSupplier'])->name('supplier.update');
    Route::delete('/supplier/{id}/destroy', [GudangController::class, 'destroySupplier'])->name('supplier.destroy');

    //RAK
    Route::get('/rak', [GudangController::class, 'indexRak'])->name('rak.index');
    Route::get('/rak/{id}/show', [GudangController::class, 'showRak'])->name('rak.show');
    Route::post('/rak', [GudangController::class, 'storeRak'])->name('rak.store');
    Route::put('/rak/{id}/update', [GudangController::class, 'updateRak'])->name('rak.update');
    Route::delete('/rak/{id}/destroy', [GudangController::class, 'destroyRak'])->name('rak.destroy');

    //BARANG
    Route::get('/barang', [GudangController::class, 'index'])->name('barang.index');
    Route::get('/barang/{id}/show', [GudangController::class, 'show'])->name('barang.show');
    Route::post('/barang', [GudangController::class, 'store'])->name('barang.store');
    Route::put('/barang/{id}/update', [GudangController::class, 'update'])->name('barang.update');
    Route::delete('/barang/{id}/destroy', [GudangController::class, 'destroy'])->name('barang.destroy');

    // Aktivitas Barang
    Route::get('/aktivitas', [AktivitasController::class, 'indexAktivitas'])->name('aktivitas.index');
    Route::post('/aktivitas', [AktivitasController::class, 'storeAktivitas'])-> name('aktivitas.store'); // Catat aktivitas
    Route::get('/aktivitas/{id}/show', [AktivitasController::class, 'show'])->name('aktivitas.show'); // Show Stok Barang
    Route::get('/aktivitas/{id}', [AktivitasController::class, 'showAktivitas']); //Show Aktivitas
    Route::put('/aktivitas/{id}/update', [AktivitasController::class, 'updateAktivitas'])->name('aktivitas.update');
    Route::delete('/aktivitas/{id}/destroy', [AktivitasController::class, 'destroyAktivitas'])->name('aktivitas.destroy');

    // Pemindahan Barang
    Route::get('/pemindahan', [AktivitasController::class, 'indexPemindahan'])->name('pemindahan.index');
    Route::post('/pemindahan', [AktivitasController::class, 'storePemindahan'])->name('pemindahan.store'); // Catat pemindahan
    Route::delete('/pemindahan/{id}/destroy', [AktivitasController::class, 'destroyPemindahan'])->name('pemindahan.destroy');

    //User
    Route::get('/user', [AuthController::class, 'index'])->name('user.indes');
    Route::post('/user', [AuthController::class, 'store'])->name('user.store');
    Route::get('/user/{id}/show', [AuthController::class, 'show'])->name('user.show');
    Route::put('/user/{id}/update', [AuthController::class, 'update'])->name('user.update');
    Route::delete('/user/{id}/destroy', [AuthController::class, 'destroy'])->name('user.destroy');

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

    //Tabel Stok Rendah
    Route::get('/stok-barang-rendah', [GudangController::class, 'getLowStockItems']);

    //Tabel Barang Expired Terdekat
    Route::get('/expired-barang-terdekat', [GudangController::class, 'checkExpires']);

    // Pie Chart Kategori yang paling banyak memiliki barang
    Route::get('/kategori-dengan-barang-terbanyak', [GudangController::class, 'kategoriDistribution']);

    // Untuk menampilkan supplier yang paling  banyak menyuplai barang ke toko
    Route::get('/chart-supplier', [GudangController::class, 'getSupplierChartData']);

    Route::get('/dashboard-total', [AktivitasController::class, 'getDashboardStats']);
    Route::post('/buang-barang/{id}', [AktivitasController::class, 'buangBarang']);
