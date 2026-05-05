<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reserva;

class NotificacionCompleta extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $requerimientosAgrupados;

public function __construct(Reserva $reserva)
{
    $this->reserva = $reserva->load(['requerimientos' => function($query) {
        $query->orderBy('tipo')->orderBy('descripcion');
    }]);
    
    // Agrupar para la vista
    $this->requerimientosAgrupados = $this->reserva->requerimientos->groupBy('tipo');
}

    public function build()
    {
        return $this->from('rreservas@colamer.edu.co', 'Sistema de Reservas')
                   ->subject("Reserva #{$this->reserva->id} - Detalles completos")
                   ->view('emails.notificacion_completa');
    }
}