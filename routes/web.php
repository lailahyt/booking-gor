<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourtController;
use App\Http\Controllers\CourtListingController;

Route::get('/', [CourtListingController::class, 'index'])->name('courts.index');
Route::get('/lapangan/{court}', [CourtListingController::class, 'show'])->name('courts.show');
Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('courts', CourtController::class)->except(['show', 'destroy']);
        Route::patch('courts/{court}/toggle', [CourtController::class, 'toggle'])->name('courts.toggle');
    });
