<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\RoleController;
use App\Models\User;



Route::middleware(['auth', 'role:admin'])->group(function () {
    // Rutas CRUD de usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('admin.usuarios.create');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('admin.usuarios.store');
    Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('admin.usuarios.edit');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('admin.usuarios.update');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('admin.usuarios.destroy');
    
    // Ruta adicional para el CRUD
    Route::get('/usuarios/crud', [UsuarioController::class, 'index'])->name('admin.usuarios.crud');

Route::resource('users', UsuarioController::class)->names('admin.users');
    
    // Rutas para roles
    Route::resource('roles', RoleController::class)->names('admin.roles');

  


});

