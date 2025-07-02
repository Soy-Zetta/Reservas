<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservaCompleta extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $requerimientos;

    public function __construct($reserva, $requerimientos)
    {
        $this->reserva = $reserva;
        $this->requerimientos = $requerimientos;
    }

    public function build()
    {
        return $this->subject("Reserva Completa #{$this->reserva->id}")
                    ->view('emails.reserva-completa');
    }
}