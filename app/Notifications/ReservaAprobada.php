<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservaAprobada extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reserva;

    public function __construct($reserva)
    {
        $this->reserva = $reserva;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('✅ Reserva Aprobada: ' . $this->reserva->nombre_actividad)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Tu reserva ha sido aprobada:')
            ->line('**Actividad:** ' . $this->reserva->nombre_actividad)
            ->line('**Fecha:** ' . $this->reserva->fecha)
            ->line('**Hora:** ' . $this->reserva->hora_inicio . ' - ' . $this->reserva->hora_fin)
            ->line('**Espacio:** ' . ($this->reserva->espacio->nombre ?? $this->reserva->otro_espacio))
            ->action('Ver Detalles', url('/reservas/' . $this->reserva->id))
            ->salutation('Saludos, Equipo de Reservas');
    }

    public function toArray($notifiable)
    {
        return [
            'reserva_id' => $this->reserva->id,
            'mensaje' => 'Tu reserva ha sido aprobada: ' . $this->reserva->nombre_actividad,
            'url' => '/reservas/' . $this->reserva->id
        ];
    }
}