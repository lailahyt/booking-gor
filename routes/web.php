<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourtController;
use App\Http\Controllers\CourtListingController;
use App\Http\Controllers\BookingController;

Route::get('/', [CourtListingController::class, 'index'])->name('courts.index');
Route::get('/lapangan/{court}', [CourtListingController::class, 'show'])->name('courts.show');
Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/lapangan/{court}/pesan', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/lapangan/{court}/pesan', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/booking', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/booking/{booking}/batal', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('courts', CourtController::class)->except(['show', 'destroy']);
        Route::patch('courts/{court}/toggle', [CourtController::class, 'toggle'])->name('courts.toggle');
    });
