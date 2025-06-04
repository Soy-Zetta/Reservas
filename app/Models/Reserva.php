<?php

namespace App\Models;
use App\Models\User;
use App\Models\Espacio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reserva extends Model
{
    use HasFactory;

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'espacio_id');
    }

    public function requerimientos()
    {
        return $this->hasMany(RequerimientoReserva::class, 'reserva_id');
    }

    //define los campos que se pueden asignar de forma masiva
    protected $fillable = [
        'usuario_id',
        'espacio_id',
        'otro_espacio',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'nombre_actividad',
        'num_personas',
        'programa_evento',
    ];

    public $timestamps = false;
}