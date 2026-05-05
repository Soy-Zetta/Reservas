<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reserva;

class NotificacionUsuario extends Mailable implements ShouldQueue  // ← agregamos la interfaz
{
    use Queueable, SerializesModels;

    public $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva->load(['requerimientos', 'espacio']);
    }

    public function build()
    {
        return $this->from('rreservas@colamer.edu.co', 'Sistema de Reservas')
                   ->subject('✅ Pre reserva Registrada - ' . $this->reserva->nombre_actividad)
                   ->view('emails.notificacion_area');  // ← cambiamos la vista
    }
}