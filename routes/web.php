<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/menu', [CatalogController::class, 'index'])->name('menu.index');
Route::get('/menu/{meal:slug}', [CatalogController::class, 'show'])->name('menu.show');
Route::get('/tim/{creator}/foto', [CreatorController::class, 'photo'])->name('creators.photo');
Route::get('/outlet', [CatalogController::class, 'outlets'])->name('outlets');
Route::get('/tentang', [CatalogController::class, 'about'])->name('about');
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'login'])->name('login');
    Route::post('/masuk', [AuthController::class, 'authenticate'])->middleware('throttle:login')->name('login.store');
    Route::get('/daftar', [AuthController::class, 'register'])->name('register');
    Route::post('/daftar', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
});
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
    Route::get('/pesan/{meal:slug}', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/pesan/{meal:slug}', [OrderController::class, 'store'])->middleware('throttle:10,1')->name('orders.store');
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order:code}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order:code}/batal', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::prefix('mitra')->middleware(EnsureAdmin::class)->name('admin.')->group(function () {
        Route::get('/tim', [CreatorController::class, 'index'])->name('creators');
        Route::post('/tim/{creator}', [CreatorController::class, 'update'])->name('creators.update');
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/menu', [AdminController::class, 'meals'])->name('meals');
        Route::get('/menu/tambah', [AdminController::class, 'create'])->name('create');
        Route::post('/menu', [AdminController::class, 'store'])->name('store');
        Route::get('/menu/{meal}/edit', [AdminController::class, 'edit'])->name('edit');
        Route::put('/menu/{meal}', [AdminController::class, 'update'])->name('update');
        Route::post('/pesanan/{order}/status', [AdminController::class, 'status'])->name('status');
    });
});
