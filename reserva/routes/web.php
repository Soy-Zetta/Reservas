<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/calendario', [ReservaController::class, 'calendario'])->name('admin.calendario');
    // Aquí puedes añadir otras rutas de administración en el futuro
});


Route::middleware(['auth'])->prefix('reservas')->group(function () {
    // ✅ Rutas fijas primero
    Route::get('/test-modal', fn() => view('test-modal'));
    Route::get('/calendario', [ReservaController::class, 'calendario'])->name('reservas.calendario');
    Route::get('/events', [ReservaController::class, 'getEvents'])->name('reservas.events');
    Route::post('/', [ReservaController::class, 'store'])->name('reservas.store');
});

Route::resource('reservas', ReservaController::class);