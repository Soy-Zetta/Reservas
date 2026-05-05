<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Espacio;
use App\Models\User;
use App\Http\Controllers\ReservaController;
use App\Models\Reserva;

class StoreReservaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'espacio_id' => 'required|exists:espacios,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $espacioId = $this->input('espacio_id');
            $fecha = $this->input('fecha');
            $horaInicio = $this->input('hora_inicio');
            $horaFin = $this->input('hora_fin');

            if (!Reserva::estaDisponible($espacioId, $fecha, $horaInicio, $horaFin)) {
                $validator->errors()->add('hora_inicio', 'El espacio ya está reservado en el horario seleccionado.');
            }
        });
    }
}

