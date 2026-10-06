<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\Userzone\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('userzone.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::scopeBindings()->group(function () {
        Route::get('/trips/{trip}/activities/create', [ActivityController::class, 'create'])->name('trips.activities.create');
        Route::post('/trips/{trip}/activities', [ActivityController::class, 'store'])->name('trips.activities.store');
        Route::get('/trips/{trip}/activities/{activity}', [ActivityController::class, 'show'])->name('trips.activities.show');
        Route::get('/trips/{trip}/activities/{activity}/edit', [ActivityController::class, 'edit'])->name('trips.activities.edit');
        Route::patch('/trips/{trip}/activities/{activity}', [ActivityController::class, 'update'])->name('trips.activities.update');
        Route::delete('/trips/{trip}/activities/{activity}', [ActivityController::class, 'destroy'])->name('trips.activities.destroy');
    });

    Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->name('trips.destroy');
    Route::patch('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
    Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
