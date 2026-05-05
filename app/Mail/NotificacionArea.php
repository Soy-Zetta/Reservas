<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reserva;

class NotificacionArea extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $reserva;
    public $area;

    public function __construct(Reserva $reserva, $area)
    {
        $this->reserva = $reserva->load(['requerimientos', 'espacio', 'usuario']);
        $this->area = $area;
    }

    public function build()
    {
        return $this->from('rreservas@colamer.edu.co', 'Sistema de Reservas')
                    ->subject("Solicitud de Pre-reserva - Área de {$this->area}")
                    ->view('emails.notificacion_usuario')
                    ->with([
                        'reserva' => $this->reserva,
                        'area' => $this->area,
                    ]);
    }
}