<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservaCompleta extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $requerimientosAgrupados;
    public $tipoArea; // 🟢 NUEVO: Guardaremos el área a la que va dirigida

    public function __construct($reserva, $requerimientosAgrupados, $tipoArea)
    {
        $this->reserva = $reserva;
        $this->requerimientosAgrupados = $requerimientosAgrupados;
        $this->tipoArea = $tipoArea; // 🟢 Asignamos la variable
    }

    public function build()
    {
        return $this->subject("Reserva Completa #{$this->reserva->id}")
                    ->view('emails.notificacion_completa'); 
    }
}