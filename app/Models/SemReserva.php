<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemReserva extends Model
{
    protected $table = 'sem_reservas'; // Nombre exacto de tu tabla
    
    protected $fillable = [
        'estado',
        'usuario_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'nombre_actividad',
        'num_personas',
        'programa_evento',
        'estado'
    ];
}