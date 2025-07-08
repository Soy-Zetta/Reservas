<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequerimientosDepartamento extends Mailable
{
    use Queueable, SerializesModels;


    public $reserva;
    public $usuario;
    public $departamento;
    public $requerimientos;
    /**
     * Create a new message instance.
     */
    public function __construct($reserva, $usuario, $departamento, $requerimientos)
    {
        //
        $this->reserva = $reserva;
        $this->usuario = $usuario;
        $this->departamento = $departamento;
        $this->requerimientos = $requerimientos;
    }


    public function build()
    {
        return $this->subject("Requerimientos de {$this->departamento} - Reserva #{$this->reserva->id}")
                    ->view('emails.requerimientos_departamento');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Requerimientos Departamento',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
