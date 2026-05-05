<?php

namespace App\Models;
use App\Models\User;
use App\Models\Espacio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reserva extends Model
{
    use HasFactory;



    public static function estaDisponible($espacioId, $fecha, $horaInicio, $horaFin, $excluirReservaId = null)
{
    return !self::where('espacio_id', $espacioId)
        ->where('fecha', $fecha)
        ->where(function ($query) use ($horaInicio, $horaFin) {
            $query->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                  ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
                  ->orWhere(function ($q) use ($horaInicio, $horaFin) {
                      $q->where('hora_inicio', '<=', $horaInicio)
                        ->where('hora_fin', '>=', $horaFin);
                  });
        })
        ->when($excluirReservaId, function ($query, $excluirReservaId) {
            $query->where('id', '!=', $excluirReservaId);
        })
        ->exists();
}


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
    return $this->hasMany(RequerimientoReserva::class, 'reserva_id')->orderBy('tipo');
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
        'estado',
        'motivo_rechazo'
    ];

    public $timestamps = false;
}