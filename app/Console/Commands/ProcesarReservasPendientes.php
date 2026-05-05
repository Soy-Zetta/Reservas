<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Reserva; // Importamos el Modelo Eloquent
use App\Mail\NotificacionAdmin;

class ProcesarReservasPendientes extends Command
{
    // Nombre del comando
    protected $signature = 'reserva:procesar {id}';
    protected $description = 'Envía correos de una reserva sin hacer esperar al usuario';

    public function handle()
    {
        $id = $this->argument('id');

        // 1. Buscamos la reserva usando el MODELO para que las relaciones funcionen
        $reserva = Reserva::find($id);

        if ($reserva) {
            try {
                // 2. Usamos la excelente función estática que ya tienes en tu Mailable
                // Ella se encargará de revisar los requerimientos y enviar a las áreas correctas
                NotificacionAdmin::enviarCorreos($reserva);
                
                // 3. Marcamos como procesado usando el Modelo (Inmune al error sem_sem_)
                $reserva->procesado = 1;
                $reserva->save();
                
                \Log::info("Correos enviados exitosamente para la reserva ID: " . $id);
            } catch (\Exception $e) {
                \Log::error("Error enviando correos reserva $id: " . $e->getMessage());
            }
        } else {
            \Log::warning("Comando procesar: No se encontró la reserva ID: " . $id);
        }
    }
}