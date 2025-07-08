<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Espacio; // Asegúrate de que este modelo exista y apunte a sem_espacios
use App\Models\RequerimientoReserva; // Asegúrate de que este modelo exista y apunte a la tabla correcta (ej. sem_requerimientos_reserva)

//MAIL
use App\Mail\ReservaConfirmada;
use App\Mail\NuevaReservaNotification;
use App\Mail\Reserva\Confirmada;
use Illuminate\Support\Facades\Log; // Útil para depuración
use Illuminate\Support\Facades\Mail;
use App\Models\Requerimiento;
use App\Mail\ReservaDepartamental;
use App\Mail\ReservaCompleta;



class ReservaController extends Controller
{
    /**
     * Guarda una nueva reserva en la base de datos.
        */
    public function store(Request $request)
    {
        // 🛡️ Validación: mínimo 3 días de anticipación
        try {
            $fechaSolicitud = Carbon::parse($request->input('fecha'));
            $minimaPermitida = now()->addDays(3)->startOfDay();

            if ($fechaSolicitud->lt($minimaPermitida)) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Las reservas deben realizarse con al menos 3 días de anticipación.'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Fecha inválida. Asegúrate de seleccionar una fecha válida para la reserva.'
            ], 400);
        }

        Log::info('ReservaController@store: Petición recibida', $request->all());

    // 1. Crear reserva PRINCIPAL
    $reserva = Reserva::create([
        'usuario_id' => Auth::id(),
        'fecha' => $request->fecha,
        'hora_inicio' => $request->hora_inicio,
        'hora_fin' => $request->input('hora_fin'),
        'nombre_actividad' => $request->input('nombre_actividad'),
        'num_personas' => $request->input('num_personas'),
        'programa_evento' => $request->input('programa_evento'),
        'aprobado' => 0,
    ]);

    // 2. Procesar ESPACIO (normal/otro)
    if ($request->espacio_id === 'Otro') {
        $reserva->update(['otro_espacio' => $request->otro_espacio]);
    }

    // 3. Procesar REQUERIMIENTOS
    $requerimientosSeleccionados = $this->procesarRequerimientos($request, $reserva);
    
    // 4. Enviar EMAILS - NUEVA LÓGICA DE NOTIFICACIONES
    try {
        // Enviar correo al solicitante
        Mail::to($user)->send(new ReservaConfirmada($reserva, 'valor_extra'));
        // Aquí continúa tu lógica de envío de correos y respuesta
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        Log::error('Error enviando correo de reserva: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Error enviando correo.']);
    }
}

// Mueve la función privada fuera del método store
private function procesarRequerimientos(Request $request, Reserva $reserva)
{
    $categorias = [
        'audiovisuales' => ['otro' => 'otro_audiovisual', 'cantidad' => 'cantidad_audiovisuales'],
        // ... otras categorías
    ];

    foreach ($categorias as $categoria => $campos) {
        if (!$request->has($categoria)) continue;
        
        foreach ($request->input($categoria) as $item) {
            $descripcion = ($item === 'Otro') 
                ? $request->input($campos['otro'])
                : $item;
                
            RequerimientoReserva::create([
                'reserva_id' => $reserva->id,
                'tipo' => $categoria,
                'descripcion' => $descripcion,
                'cantidad' => $request->input("{$campos['cantidad']}.{$item}"),
            ]);
        }
    }
}


    /**
     * Muestra el formulario de creación de reservas.
     */
    public function create()
    {
        $espacios = Espacio::all(); // Cargar todos los espacios disponibles desde la base de datos
        return view('reservas.create', compact('espacios')); // Asegúrate de que la vista se llame 'reservas.create' o ajusta según tu proyecto
        $requerimientosPorDepartamento = Requerimiento::all()
            ->groupBy('departamento');
        
        return view('reservas.create', [
            'departamentos' => $requerimientosPorDepartamento
        ]);

    }

    /**
     * Devuelve las reservas en formato JSON para FullCalendar del usuario actual.
     */
    public function getReservations() // Nota: este método no se usa actualmente para poblar el calendario general
    {
        $reservas = Reserva::where('usuario_id', Auth::id())->get();
        $events = [];

        foreach ($reservas as $reserva) {
            $events[] = [
                'id' => $reserva->id,
                'title' => $reserva->nombre_actividad,
                'start' => "{$reserva->fecha}T{$reserva->hora_inicio}",
                'end' => "{$reserva->fecha}T{$reserva->hora_fin}",
            ];
        }
        return response()->json($events);
    }

    /**
     * Muestra la vista del calendario.
     */
    public function calendario()
    {
        $espacios = Espacio::all();
        return view('calendario', compact('espacios'));
    }

    /**
     * Devuelve todos los eventos en formato JSON para FullCalendar.
     */
// En app/Http/Controllers/ReservaController.php

public function getEvents()
{
    Log::info('ReservaController@getEvents: Obteniendo eventos para el calendario.');
    $reservas = Reserva::with(['usuario', 'espacio', 'requerimientos'])->get();
    $eventos = [];

    foreach ($reservas as $reserva) {
        $requerimientosFormateados = [];
        if ($reserva->requerimientos) {
            foreach ($reserva->requerimientos as $req) {
                $requerimientosFormateados[] = [
                    'descripcion' => $req->descripcion,
                    'cantidad' => $req->cantidad,
                ];
            }
        }

        $eventos[] = [
            'id' => $reserva->id,
            'title' => $reserva->nombre_actividad,
            'start' => "{$reserva->fecha}T{$reserva->hora_inicio}",
            'end' => "{$reserva->fecha}T{$reserva->hora_fin}",

          
            // Estos datos estarán disponibles en JavaScript como info.event.extendedProps
            'extendedProps' => [
                // Para el tooltip: "Usuario:"
                'usuario_nombre' => $reserva->usuario ? $reserva->usuario->name : 'No disponible', // Asume que el modelo User tiene un atributo 'name'
                
                // Para el tooltip: "Espacio:"
                'espacio_nombre' => $reserva->espacio ? $reserva->espacio->nombre : ($reserva->otro_espacio ?: 'No especificado'),
                
                // Para el tooltip: "Número de Personas:"
                'num_personas' => $reserva->num_personas,
                
                // Podría ser útil para el modal de detalles (eventClick), no directamente para el tooltip actual
                'programa_evento' => $reserva->programa_evento, 
                
                // Para el tooltip: "Requerimientos:"
                'requerimientosArray' => $requerimientosFormateados, // Array de requerimientos ya formateado
                
                // Puedes añadir cualquier otro campo de la $reserva que necesites aquí para el tooltip
            ]
        ];
    }
    Log::info('ReservaController@getEvents: Eventos formateados enviados.', ['count' => count($eventos)]);
    return response()->json($eventos);
}
    /**
     * Muestra los detalles de una reserva.
     */
    public function show(Reserva $reserva) 
    {
        // Carga las relaciones que quieres mostrar
        $reserva = Reserva::with(['usuario', 'espacio', 'requerimientos'])->findOrFail($reserva->id);
        return response()->json($reserva);
    }
 
    public function update(Request $request, Reserva $reserva)
    {
        

        // Validaciones (ejemplo, ajústalas según necesidad)
        $request->validate([
            'espacio_id' => 'required_without:otro_espacio|nullable|exists:sem_espacios,id', // Si usas sem_espacios
            'otro_espacio' => 'required_without:espacio_id|nullable|string|max:255',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'nombre_actividad' => 'required|string|max:255',
            'num_personas' => 'required|integer|min:1',
            'programa_evento' => 'nullable|string|max:255',
        ]);

        $datosUpdate = $request->only([
            'fecha', 'hora_inicio', 'hora_fin', 'nombre_actividad', 'num_personas', 'programa_evento'
        ]);

        $selectedEspacioId = $request->input('espacio_id');
        $otroEspacioValue = $request->input('otro_espacio');

        if ($selectedEspacioId === 'Otro') {
            $datosUpdate['espacio_id'] = null;
            $datosUpdate['otro_espacio'] = filled($otroEspacioValue) ? trim($otroEspacioValue) : null;
        } else {
            $datosUpdate['espacio_id'] = $selectedEspacioId;
            $datosUpdate['otro_espacio'] = null;
        }
        
        $reserva->update($datosUpdate);

        // Actualizar requerimientos: usualmente es más fácil borrarlos y recrearlos
        $reserva->requerimientos()->delete(); // Borra los requerimientos antiguos

        // Re-crea los requerimientos (misma lógica que en store)
        $categoriasRequerimientos = [
            'audiovisuales'       => ['otro_campo_texto' => 'otro_audiovisual',       'campo_cantidad' => 'cantidad_audiovisuales'],
            'servicios_generales' => ['otro_campo_texto' => 'otro_servicio_general',  'campo_cantidad' => 'cantidad_servicios_generales'],
            'comunicaciones'      => ['otro_campo_texto' => 'otro_comunicacion',      'campo_cantidad' => 'cantidad_comunicaciones'],
            'administracion'      => ['otro_campo_texto' => 'otro_administracion',      'campo_cantidad' => 'cantidad_administracion'],
        ];

        foreach ($categoriasRequerimientos as $nombreCategoriaInput => $nombresCampos) {
            // ... (misma lógica de bucle y creación de RequerimientoReserva que en el método store) ...
            // Copia el bucle interno de la función store aquí para procesar los requerimientos.
            // Es importante que esta lógica sea idéntica o muy similar.
            // Para no repetir código, podrías mover la lógica de guardar requerimientos
            // a un método privado dentro de este controlador, y llamarlo desde store() y update().
            // Por ejemplo: $this->sincronizarRequerimientos($request, $reserva);
        }

        // return redirect()->route('reservas.index')->with('success', 'Reserva actualizada correctamente.'); // Si fuera una app tradicional
        return response()->json(['message' => 'Reserva actualizada con éxito', 'reserva' => $reserva->load('requerimientos')]);
    }
    
    public function destroy(Reserva $reserva)
    {
        // $this->authorize('delete', $reserva); // Si usas Policies
        
        // Los requerimientos se borrarán en cascada si la FK está bien definida en la BD
        $reserva->delete();
        
        // return redirect()->route('reservas.index')->with('success', 'Reserva eliminada correctamente.'); // Si fuera una app tradicional
        return response()->json(['message' => 'Reserva eliminada con éxito']);
    }










    // Método privado sugerido para no repetir código de requerimientos:
    // private function sincronizarRequerimientos(Request $request, Reserva $reserva)
    // {
    //     $reserva->requerimientos()->delete(); // Borra los antiguos

    //     $categoriasRequerimientos = [ /* ... tu array de categorías ... */ ];
    //     foreach ($categoriasRequerimientos as $nombreCategoriaInput => $nombresCampos) {
    //         $otroCampoTextoNombre = $nombresCampos['otro_campo_texto'];
    //         $cantidadArrayNombre = $nombresCampos['campo_cantidad'];

    //         if ($request->has($nombreCategoriaInput)) {
    //             foreach ($request->input($nombreCategoriaInput) as $itemSeleccionado) {
    //                 $descripcionFinal = $itemSeleccionado;
    //                 $cantidadFinal = $request->input("{$cantidadArrayNombre}.{$itemSeleccionado}");

    //                 if ($itemSeleccionado === 'Otro') {
    //                     $textoOtroEspecifico = $request->input($otroCampoTextoNombre);
    //                     if ($request->filled($otroCampoTextoNombre) && !empty(trim($textoOtroEspecifico))) {
    //                         $descripcionFinal = trim($textoOtroEspecifico);
    //                     } else {
    //                         continue; 
    //                     }
    //                 }
                    
    //                 if (!empty($descripcionFinal)) {
    //                     RequerimientoReserva::create([
    //                         'reserva_id' => $reserva->id,
    //                         'tipo' => $nombreCategoriaInput,
    //                         'descripcion' => $descripcionFinal,
    //                         'cantidad' => $cantidadFinal,
    //                     ]);
    //                 }
    //             }
    //         }
    //     }
    // }
}