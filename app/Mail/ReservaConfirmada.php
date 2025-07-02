<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservaConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $usuario;

    public function __construct($reserva, $usuario)
    {
        $this->reserva = $reserva;
        $this->usuario = $usuario;
    }

    public function build()
    {
        return $this->subject('Confirmación de Reserva')
                    ->view('emails.reserva_confirmada');
    }
}