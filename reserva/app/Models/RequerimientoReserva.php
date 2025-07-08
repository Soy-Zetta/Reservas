<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequerimientoReserva extends Model
{
    use HasFactory;

    protected $table = 'requerimientos_reserva';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reserva_id',
        'tipo',
        'descripcion',
        'cantidad',
    ];

    /**
     * Define si el modelo debe gestionar los timestamps (created_at y updated_at).
     * Como la tabla los tiene, los dejamos activados (es el comportamiento por defecto).
     * Si no quisieras que Eloquent los gestione, pondrías: public $timestamps = false;
     */
    // public $timestamps = true; // Esta línea es opcional, true es el valor por defecto

    /**
     * Obtiene la reserva a la que pertenece este requerimiento.
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}