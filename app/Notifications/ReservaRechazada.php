<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservaRechazada extends Notification
{
    use Queueable;

    protected $reserva;
    protected $razon;

    public function __construct($reserva, $razon = null)
    {
        $this->reserva = $reserva;
        $this->razon = $razon;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('❌ Reserva Rechazada: ' . $this->reserva->nombre_actividad)
            ->greeting('Hola ' . $notifiable->name)
            ->line('Lamentamos informarte que tu reserva ha sido rechazada:');
        
        if ($this->razon) {
            $mail->line('**Razón:** ' . $this->razon);
        }
        
        return $mail->action('Ver Alternativas', url('/calendario'))
            ->line('Puedes intentar reservar otro espacio u horario')
            ->salutation('Atentamente, Equipo de Reservas');
    }
}