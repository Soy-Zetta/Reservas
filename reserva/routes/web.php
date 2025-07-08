<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;
use App\Mail\ReservaMail;

//MAIL
use App\Mail\PruebaMail;
use Illuminate\Support\Facades\Mail;


// Rutas para administración de reservas
Route::prefix('admin')->group(function () {
    Route::get('/reservas', [ReservaController::class, 'index'])->name('admin.reservas.index');
    Route::post('/reservas/{reserva}/aprobar', [ReservaController::class, 'aprobar'])->name('reservas.aprobar');
    Route::post('/reservas/{reserva}/rechazar', [ReservaController::class, 'rechazar'])->name('reservas.rechazar');
});


Route::get('/test-email', function () {
    Mail::to('jeison0603k@gmail.com')->send(new App\Mail\TestEmail());
    return "Correo HTML enviado!";
});
//MAIL
Route::get('/enviar-correo', function () {
    Mail::to('jeison0603k@gmail.com')->send(new PruebaMail());
    return "Correo enviado.";
});



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
    Route::get('/calendario', [ReservaController::class, 'calendario'])->name('reservas.calendario');
    Route::get('/events', [ReservaController::class, 'getEvents'])->name('reservas.events');
    Route::post('/', [ReservaController::class, 'store'])->name('reservas.store');
    Route::get('/{reserva}', [ReservaController::class, 'show'])->name('reservas.show');
    Route::get('/{reserva}/edit', [ReservaController::class, 'edit'])->name('reservas.edit');
    Route::put('/{reserva}', [ReservaController::class, 'update'])->name('reservas.update');
    Route::delete('/{reserva}', [ReservaController::class, 'destroy'])->name('reservas.destroy');
});