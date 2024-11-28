<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GudangController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


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

