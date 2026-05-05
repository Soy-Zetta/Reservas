<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reserva;

class NotificacionEspecifica extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $tipoRequerimiento;
    public $requerimientosFiltrados;

    public function __construct(Reserva $reserva, string $tipoRequerimiento)
    {
        $this->reserva = $reserva;
        $this->tipoRequerimiento = $tipoRequerimiento;
        $this->requerimientosFiltrados = $reserva->requerimientos
            ->where('tipo', $tipoRequerimiento)
            ->groupBy('tipo');
    }

    public function build()
    {
        return $this->from('rreservas@colamer.edu.co', 'Sistema de Reservas')
                   ->subject("Requerimiento de {$this->tipoRequerimiento} - Reserva #{$this->reserva->id}")
                   ->view('emails.notificacion_especifica');
    }
}