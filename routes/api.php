<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/verificar-disponibilidad', function (Request $request) {
    $disponible = Reserva::estaDisponible(
        $request->espacio_id,
        $request->fecha,
        $request->hora_inicio,
        $request->hora_fin
    );
    return response()->json(['disponible' => $disponible]);
});