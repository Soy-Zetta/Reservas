<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reserva;

class ReservaRechazada extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $motivo;

    public function __construct(Reserva $reserva, $motivo)
    {
        $this->reserva = $reserva;
        $this->motivo = $motivo;
    }

    public function build()
    {
        return $this->subject('❌ Tu reserva ha sido rechazada')
                   ->view('emails.reserva_rechazada');
    }
}