<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // Importamos la fachada de correo
use App\Models\Reserva; // 🟢 Importamos el Modelo para que coincida con tu Mailable
use App\Mail\ReservaAprobada; // 🟢 Importamos el Mailable que acabas de crear
use Carbon\Carbon;

class AutoAprobarReservas extends Command
{
    protected $signature = 'reservas:auto-aprobar';
    protected $description = 'Aprueba automáticamente las reservas que no han sido respondidas en 24 horas HÁBILES';

    public function handle()
    {
        $this->info("🤖 Iniciando revisión de reservas vencidas (Días Hábiles)...");

        $fechaLimite = Carbon::now()->subWeekday();

        $this->info("Buscando reservas pendientes creadas antes de: " . $fechaLimite->toDateTimeString());

        // 1. Usamos Eloquent (Reserva::) para obtener objetos modelo en lugar de stdClass
        $reservasVencidas = Reserva::where('aprobado', 0)
            ->where('created_at', '<=', $fechaLimite)
            ->get();

        if ($reservasVencidas->isEmpty()) {
            $this->info("✅ No hay reservas vencidas pendientes.");
            return; // Termina la ejecución si no hay trabajo
        }

        foreach ($reservasVencidas as $reserva) {
            $this->info("Procesando Reserva ID: " . $reserva->id);

            // 2. Actualizamos los requerimientos internos pendientes a aprobados (1)
            DB::table('requerimientos_reserva')
                ->where('reserva_id', $reserva->id)
                ->where('estado', 0)
                ->update(['estado' => 1]);

            // 3. Revisamos si hubo algún rechazo previo por parte de otra área
            $hayRechazados = DB::table('requerimientos_reserva')
                ->where('reserva_id', $reserva->id)
                ->where('estado', -1)
                ->exists();

            $nuevoEstadoGlobal = $hayRechazados ? 2 : 1; 

            // Actualizamos el estado de la reserva directamente con el modelo
            $reserva->aprobado = $nuevoEstadoGlobal;
            $reserva->save();

            $this->info("➡️ Reserva {$reserva->id} actualizada a estado global: {$nuevoEstadoGlobal}");

            // 4. 🟢 DISPARAMOS EL CORREO AL USUARIO
            // Evaluamos si el estado global quedó en 1 (Verde/Aprobado totalmente)
            if ($nuevoEstadoGlobal == 1) {
                try {
                    // Accedemos al correo mediante la relación usuario
                    Mail::to($reserva->usuario->email)->send(new ReservaAprobada($reserva));
                    $this->info("📧 Correo de aprobación enviado con éxito al usuario: " . $reserva->usuario->email);
                } catch (\Exception $e) {
                    $this->error("❌ Fallo crítico enviando correo al usuario de la reserva {$reserva->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("🏁 Proceso terminado.");
    }
}