<?php

namespace App\Models;
use App\Models\User;
use App\Models\Espacio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\ReservaAprobada;
use App\Notifications\ReservaRechazada;




class Reserva extends Model
{
    // ...

    // Estados posibles
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADO = 'aprobado';
    const ESTADO_RECHAZADO = 'rechazado';

    protected $attributes = [
        'estado' => self::ESTADO_PENDIENTE,
    ];

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con espacio
    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'espacio_id');
    }


    // Aprobar reserva
    public function aprobar()
    {
        $this->estado = self::ESTADO_APROBADO;
        $this->save();
        
        // Notificar al usuario
        $this->user->notify(new ReservaAprobada($this));
        
        return $this;
    }

    // Rechazar reserva
    public function rechazar($razon = null)
    {
        $this->estado = self::ESTADO_RECHAZADO;
        $this->save();
        
        // Notificar al usuario
        $this->user->notify(new ReservaRechazada($this, $razon));
        
        return $this;
    }



    use HasFactory;

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // (Método espacio ya definido arriba, se elimina duplicado)

    public function requerimientos()
    {
        return $this->belongsToMany(Requerimiento::class);
    }
    //define los campos que se pueden asignar de forma masiva
    protected $fillable = [
        'estado',
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