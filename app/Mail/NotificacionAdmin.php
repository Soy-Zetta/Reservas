<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reserva;
use Illuminate\Support\Facades\Mail;

class NotificacionAdmin extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $reserva;
    public $requerimientosFiltrados;
    public $tipoCorreo;
    public $esSolicitante; // 🟢 NUEVO: Variable para identificar al dueño de la reserva

    
    const CORREOS_ESPECIFICOS = [
        'audiovisuales' => 'raudiovisuales@colamer.edu.co', 
        'comunicaciones' => 'rcomunicaciones@colamer.edu.co',
        'administracion' => 'radministracion@colamer.edu.co',
        'servicios_generales' => 'rservicios@colamer.edu.co'
    ];

    // Biblioteca queda como general (si no tiene items de selección en el formulario)
    const CORREOS_GENERALES = [
        'rbiblioteca@colamer.edu.co'
    ];

    // 🟢 NUEVO: Recibimos el parámetro $esSolicitante (por defecto es falso)
    public function __construct(Reserva $reserva, string $tipoCorreo = null, bool $esSolicitante = false)
    {
        $this->reserva = $reserva->load('requerimientos');
        $this->tipoCorreo = $tipoCorreo;
        $this->esSolicitante = $esSolicitante;

        $this->requerimientosFiltrados = $tipoCorreo 
            ? $reserva->requerimientos->where('tipo', $tipoCorreo)
            : $reserva->requerimientos;
    }

    public function build()
    {
        $subject = '[Reserva #' . $this->reserva->id . '] Nueva Solicitud - ' . now()->format('d/m/Y');
        
        if ($this->tipoCorreo) {
            $subject .= ' (' . ucfirst(str_replace('_', ' ', $this->tipoCorreo)) . ')';
        }

        return $this->from('rreservas@colamer.edu.co', 'Sistema de Reservas')
                   ->subject($subject)
                   ->view('emails.notificacion_admin');
    }

    public static function enviarCorreos(Reserva $reserva)
    {
        // 1. Enviar a áreas específicas (NO son solicitantes, esSolicitante = false)
        foreach (self::CORREOS_ESPECIFICOS as $tipo => $correo) {
            if ($reserva->requerimientos->where('tipo', $tipo)->isNotEmpty()) {
                Mail::to($correo)->send(new self($reserva, $tipo, false));
            }
        }

        // 2. Enviar a generales como audiovisuales (NO son solicitantes, esSolicitante = false)
        foreach (self::CORREOS_GENERALES as $correo) {
            Mail::to($correo)->send(new self($reserva, null, false));
        }

        // 3. Enviar al dueño de la reserva (SÍ es solicitante, esSolicitante = true)
        if ($reserva->usuario && $reserva->usuario->email) {
            Mail::to($reserva->usuario->email)->send(new self($reserva, null, true));
        }
    }
}