<?php

use App\Http\Controllers\ReservaController;
use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use App\Models\Reserva;





// Página principal (accesible por todos)
Route::get('/', [IndexController::class, 'index'])->name('index');

// Rutas de reservas

Route::middleware(['auth'])->prefix('reservas')->group(function () { 

    // Rutas para ver listado, calendario, eventos, mostrar reserva, guardar (sin middleware 'role')
    Route::get('/', [ReservaController::class, 'index'])->name('reservas.index');
    Route::post('/', [ReservaController::class, 'store'])->name('reservas.store');
    Route::get('/calendario', [ReservaController::class, 'calendario'])->name('reservas.calendario');
    Route::get('/events', [ReservaController::class, 'getEvents'])->name('reservas.events');
    Route::get('/{reserva}', [ReservaController::class, 'show'])->name('reservas.show');

    // Ruta para eliminar reserva (sin middleware 'role' aquí, la protección estará en el controlador con Gate)
    Route::delete('/{reserva}', [ReservaController::class, 'destroy'])->name('reservas.destroy');

    // Estas rutas estaban comentadas y no tenían middleware 'role' aplicado correctamente de todas formas:
    // Route::get('/{reserva}/edit', [ReservaController::class, 'edit'])->name('reservas.edit'); // Aquí tampoco middleware 'role'
    // Route::put('/{reserva}', [ReservaController::class, 'update'])->name('reservas.update'); // Aquí tampoco middleware 'role'
});


// Rutas de autenticación
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');//cerrar sesion
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login');


// Rutas de dashboards (protegidas solo por 'auth', la protección de roles será con Gates/Policies dentro del controlador/vista)
Route::middleware(['auth'])->group(function () { // Solo middleware 'auth'
    // Restauramos la definición de la ruta /admin
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

Route::middleware(['auth'])->group(function () { // Solo middleware 'auth'
    // Restauramos la definición de la ruta /usuario
    Route::get('/usuario', function () {
        return view('usuario.dashboard');
    })->name('usuario.dashboard');
});

// Esta línea estaba comentada y no es necesaria si defines auth rutas manualmente:
//Auth::routes();

// Esta ruta tampoco tenía middleware 'role', no necesita cambios:
Route::get('/home', [App\Http\Controllers\IndexController::class, 'index'])->name('home');