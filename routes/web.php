<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\UsuarioController;

// 1. Rutas Públicas
Route::get('/', function () {
    return redirect('/login');
});

// 2. Rutas Protegidas (Requieren Login)
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // --- SECCIÓN DE RESERVAS ---
    Route::prefix('reservas')->group(function () {
        Route::get('/events', [ReservaController::class, 'getEvents'])->name('reservas.events');
        Route::get('/calendario', [ReservaController::class, 'calendario'])->name('reservas.calendario');
        Route::post('/check-disponibilidad', [ReservaController::class, 'checkDisponibilidad']);
        Route::post('/', [ReservaController::class, 'store'])->name('reservas.store');
        
        // Rutas con firma (Correos)
        Route::middleware(['signed'])->group(function () {
            Route::get('/{reserva}/aprobar/{tipo}', [ReservaController::class, 'aprobar'])->name('reservas.aprobar');
            Route::get('/{reserva}/rechazar/{tipo}', [ReservaController::class, 'rechazar'])->name('reservas.rechazar');
        });

        // Rutas de administración de una reserva específica
        Route::get('/{id}', [ReservaController::class, 'show'])->name('reservas.show');
        Route::put('/{id}', [ReservaController::class, 'update'])->name('reservas.update');
        Route::post('/{reserva}/forzar/{estado}', [ReservaController::class, 'forzarEstado'])->name('reservas.forzar');
    });

    Route::post('/reservas/eliminar-accion', [ReservaController::class, 'destroy'])->name('reservas.destroy.final');

   // --- SECCIÓN DE ADMINISTRACIÓN ---
    // 🟢 Quitamos el middleware estricto de Spatie de aquí para que el controlador decida.
    Route::prefix('admin')->group(function () {
        
        Route::get('/calendario', [ReservaController::class, 'calendario'])->name('admin.calendario');
        Route::get('/dashboard', function () { return view('admin.dashboard'); });
        
        // 🟢 EL TRUCO PARA TU MENÚ: Le decimos explícitamente a Laravel que "crear" es la vista de creación, no un ID.
        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.crear.es');
        
        // CRUD DE USUARIOS
        Route::resource('usuarios', UsuarioController::class)->except(['show'])->names('usuarios');
    });
});