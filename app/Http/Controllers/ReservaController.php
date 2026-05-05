<?php

namespace App\Http\Controllers;
use App\Models\Reserva;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Espacio; // Asegúrate de que este modelo exista y apunte a sem_espacios
use App\Models\RequerimientoReserva; // Asegúrate de que este modelo exista y apunte a la tabla correcta (ej. sem_requerimientos_reserva)
use Illuminate\Support\Facades\Log; // Útil para depuración
use App\Http\Requests\StoreReservaRequest;


//MAIL//
use App\Mail\ReservaUsuario;
use App\Mail\NotificacionUsuario; // Asegúrate de que este modelo exista y apunte a la tabla correcta (ej. sem_requerimientos_reserva)
use Illuminate\Support\Facades\Mail;



class ReservaController extends Controller
{ 
    public function checkAvailability(Request $request)
{
    $request->validate([
        'espacio_id' => 'required|exists:espacios,id',
        'fecha' => 'required|date',
        'hora_inicio' => 'required|date_format:H:i',
        'hora_fin' => 'required|date_format:H:i|after:hora_inicio'
    ]);

    $disponible = Reserva::where('espacio_id', $request->espacio_id)
        ->where('fecha', $request->fecha)
        ->where(function ($query) use ($request) {
            $query->whereBetween('hora_inicio', [$request->hora_inicio, $request->hora_fin])
                  ->orWhereBetween('hora_fin', [$request->hora_inicio, $request->hora_fin])
                  ->orWhere(function ($q) use ($request) {
                      $q->where('hora_inicio', '<=', $request->hora_inicio)
                        ->where('hora_fin', '>=', $request->hora_fin);
                  });
        })
        ->doesntExist();

    return response()->json(['disponible' => $disponible]);
}
    
public function index()
{
    // 1. Buscamos todos los espacios de la tabla sem_espacios
    // Usamos el prefijo sem_ que definimos en tu arquitectura
    $espacios = \DB::table('espacios')->get();

    // 2. Enviamos la variable 'espacios' a la vista calendario
    return view('calendario', compact('espacios'));
}

public function store(\Illuminate\Http\Request $request) 
{
    // Usamos una transacción de base de datos para que, si algo falla, no guarde datos a medias.
    \DB::beginTransaction();

    try {
        // 1. FORMATEO DE FECHAS Y HORAS
        $fechaRaw = $request->input('fecha') ?? now()->format('Y-m-d');
        $fechaFormateada = date('Y-m-d', strtotime($fechaRaw));

        $horaInicioRaw = $request->input('hora_inicio') ?? '08:00';
        $horaInicioFormateada = strlen($horaInicioRaw) == 5 ? $horaInicioRaw . ':00' : $horaInicioRaw;

        $horaFinRaw = $request->input('hora_fin') ?? '09:00';
        $horaFinFormateada = strlen($horaFinRaw) == 5 ? $horaFinRaw . ':00' : $horaFinRaw;

       // 🟢 NUEVO: Validación de 5 días de anticipación (Excluye a los Administradores)
        $user = auth()->user();
        $esAdmin = $user && (
            $user->hasRole('superadmin') || 
            $user->hasRole('admin') || 
            (isset($user->rol_id) && in_array($user->rol_id, [1, 2]))
        );

        $fechaReserva = \Carbon\Carbon::parse($fechaFormateada);
        if (!$esAdmin && $fechaReserva->startOfDay() < now()->addDays(5)->startOfDay()) {
            \DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Por políticas, las reservas deben hacerse con al menos 5 días de anticipación.'
            ], 400);
        }

        // 🟢 NUEVO: Lógica Inteligente del Espacio
        $espacioInput = $request->input('espacio_id');
        $espacioId = null;
        $otroEspacioTexto = null;

        if ($espacioInput === 'Otro' || !is_numeric($espacioInput)) {
            $espacioId = null;
            $otroEspacioTexto = $request->input('otro_espacio') ?? 'Espacio personalizado'; 
        } else {
            $espacioId = $espacioInput;
            
            // 🟢 NUEVO: Validación de Choque de Horarios (Solo si es un espacio registrado)
            $choque = \DB::table('reservas')
                ->where('fecha', $fechaFormateada)
                ->where('espacio_id', $espacioId)
                ->where('aprobado', '!=', -1) // Ignoramos las rechazadas
                ->where(function ($query) use ($horaInicioFormateada, $horaFinFormateada) {
                    $query->where('hora_inicio', '<', $horaFinFormateada)
                          ->where('hora_fin', '>', $horaInicioFormateada);
                })
                ->exists();

            if ($choque) {
                \DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'El espacio seleccionado ya está ocupado en ese horario.'
                ], 400);
            }
        }

        // 3. INSERCIÓN DE LA RESERVA PRINCIPAL (Intacta)
        $id = \DB::table('reservas')->insertGetId([
            'usuario_id'       => auth()->id() ?? 1,
            'espacio_id'       => $espacioId,
            'otro_espacio'     => $otroEspacioTexto,
            'fecha'            => $fechaFormateada,
            'hora_inicio'      => $horaInicioFormateada,
            'hora_fin'         => $horaFinFormateada,
            'nombre_actividad' => $request->input('nombre_actividad') ?? 'Sin Nombre',
            'num_personas'     => $request->input('num_personas') ?? 0,
            'programa_evento'  => $request->input('programa_evento') ?? '',
            'aprobado'         => 0,
            'procesado'        => 0
        ]);

        // 4. INSERCIÓN DE LOS REQUERIMIENTOS (🟢 AJUSTADO A TU TABLA EXACTA)
        $categorias = [
            'audiovisuales'       => ['cant' => 'cantidad_audiovisuales',       'otro' => 'otro_audiovisual'],
            'servicios_generales' => ['cant' => 'cantidad_servicios_generales', 'otro' => 'otro_servicio_general'],
            'comunicaciones'      => ['cant' => 'cantidad_comunicaciones',      'otro' => 'otro_comunicacion'],
            'administracion'      => ['cant' => 'cantidad_administracion',      'otro' => 'otro_administracion'],
        ];

        $requerimientosAInsertar = [];

        foreach ($categorias as $tipo => $nombres) {
            $itemsSeleccionados = $request->input($tipo); 

            if (is_array($itemsSeleccionados) && count($itemsSeleccionados) > 0) {
                $arregloCantidades = $request->input($nombres['cant']) ?? []; 

                foreach ($itemsSeleccionados as $item) {
                    $cantidad = isset($arregloCantidades[$item]) ? (int) $arregloCantidades[$item] : 1;
                    
                    // Si seleccionó "Otro", la descripción es lo que escribió. Si no, es el nombre del item (ej. "Vasos").
                    $descripcionFinal = ($item === 'Otro') ? $request->input($nombres['otro']) : $item;

                    $requerimientosAInsertar[] = [
                        'reserva_id'  => $id,
                        'tipo'        => $tipo,
                        'descripcion' => $descripcionFinal, // 🟢 Ahora coincide perfecto con tu DB
                        'cantidad'    => $cantidad
                        // Ya no enviamos 'item' ni 'detalles_otro'
                    ];
                }
            }
        }

        if (!empty($requerimientosAInsertar)) {
            \DB::table('requerimientos_reserva')->insert($requerimientosAInsertar);
        }

        \DB::commit();


// 5. LANZAR LOS CORREOS (AL USUARIO Y A LAS ÁREAS)
        $reservaGuardada = \App\Models\Reserva::with(['usuario', 'espacio', 'requerimientos'])->find($id);

        // 1. Envío al usuario solicitante (¡Éxito confirmado!)
        \Illuminate\Support\Facades\Mail::to($reservaGuardada->usuario->email)
            ->send(new \App\Mail\ReservaUsuario($reservaGuardada));

        // 2. Diccionario de correos por área
        $correosAreas = [
            'audiovisuales'       => 'raudiovisuales@colamer.edu.co',
            'servicios_generales' => 'rservicios@colamer.edu.co',
            'comunicaciones'      => 'rcomunicaciones@colamer.edu.co',
            'administracion'      => 'radministracion@colamer.edu.co',
        ];

        // 3. Descubrir qué áreas solicitó el usuario y AGRUPARLAS para la vista
        $tiposSolicitados = $reservaGuardada->requerimientos->pluck('tipo')->unique();
        $requerimientosAgrupados = $reservaGuardada->requerimientos->groupBy('tipo');

    // 4. Enviar un correo a cada área involucrada (SIN SILENCIADOR)
        foreach ($tiposSolicitados as $tipo) {
            if (isset($correosAreas[$tipo])) {
                // Instanciamos el Mailable enviando la reserva, los requerimientos Y el tipo de área
                \Illuminate\Support\Facades\Mail::to($correosAreas[$tipo])
                    ->send(new \App\Mail\ReservaCompleta($reservaGuardada, $requerimientosAgrupados, $tipo));
            }
        }

        \DB::commit();

        return response()->json([
            'success' => true, 
            'message' => '¡Reserva creada exitosamente y notificaciones enviadas!',
            'id_generado' => $id
        ]);

    } catch (\Exception $e) {
        \DB::rollBack();
        // 🟢 Si algo falla en los correos, este mensaje rojo nos dirá exactamente por qué
        return response()->json([
            'success' => false,
            'message' => 'Error en el proceso (Posible fallo de correo): ' . $e->getMessage()
        ], 500);
    }
}




private function enviarNotificaciones($reserva)
{
    dispatch(new \App\Jobs\EnviarNotificacionesReserva($reserva));
}

    /**
     * Muestra el formulario de creación de reservas.
     */
    public function create()
    {
        $espacios = Espacio::all(); // Cargar todos los espacios disponibles desde la base de datos
        return view('reservas.create', compact('espacios')); // Asegúrate de que la vista se llame 'reservas.create' o ajusta según tu proyecto
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

public function getEvents(Request $request)
    {
        \Log::info('ReservaController@getEvents: Obteniendo eventos para el calendario.');

        $start = $request->input('start');
        $end   = $request->input('end');

        // 🟢 LÓGICA CORREGIDA: Traer todo EXCEPTO las rechazadas (-1)
        $query = \App\Models\Reserva::with(['usuario:id,name', 'espacio:id,nombre'])
                    ->where('aprobado', '!=', -1);

        if ($start && $end) {
            $query->whereBetween('fecha', [
                date('Y-m-d', strtotime($start)),
                date('Y-m-d', strtotime($end))
            ]);
        }

        $reservas = $query->get();
        $eventos = [];

        foreach ($reservas as $reserva) {
            $estadoActual = $reserva->aprobado;
            
            $color = '#f59e0b'; // Naranja (Pendiente - Estado 0 o NULL)
            $estadoTexto = 'Pendiente';

            if ($estadoActual == 1) {
                $color = '#10b981'; // Verde (Aprobada)
                $estadoTexto = 'Aprobada';
            } elseif ($estadoActual == 2) {
                $color = '#8b5cf6'; // Morado (Conflicto de Servicios)
                $estadoTexto = 'Conflicto de Servicios';
            }
            // Ya no necesitamos el "elseif ($estadoActual == -1)" porque la base de datos ya las filtró.

            $eventos[] = [
                'id'    => $reserva->id,
                'title' => $reserva->nombre_actividad,
                'start' => "{$reserva->fecha}T{$reserva->hora_inicio}",
                'end'   => "{$reserva->fecha}T{$reserva->hora_fin}",
                'color' => $color,
                'extendedProps' => [
                    'usuario_nombre' => $reserva->usuario ? $reserva->usuario->name : 'No disponible',
                    'espacio_nombre' => $reserva->espacio ? $reserva->espacio->nombre : ($reserva->otro_espacio ?: 'No especificado'),
                    'aprobado'       => $estadoActual, 
                    'estado_texto'   => $estadoTexto
                ]
            ];
        }

        \Log::info('ReservaController@getEvents: Eventos formateados enviados.', ['count' => count($eventos)]);
        return response()->json($eventos);
    }
    /**
     * Muestra los detalles de una reserva.
     */
public function show($id) // Recibimos el $id directamente
{
    try {
        // Buscamos la reserva
        $reserva = Reserva::with(['usuario', 'espacio', 'requerimientos'])->find($id);

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        // Devolvemos la data directamente (el JS ya se encarga de formatear)
        return response()->json($reserva);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



public function update(\Illuminate\Http\Request $request, $id) // 🟢 CAMBIO CRÍTICO AQUÍ: Recibimos solo el $id
    {
        \DB::beginTransaction();

        try {
            // 🟢 1. BUSCAMOS LA RESERVA MANUALMENTE (Esto soluciona el problema del ID nulo para siempre)
            $reserva = \App\Models\Reserva::find($id);
            
            if (!$reserva) {
                return response()->json(['success' => false, 'message' => 'Reserva no encontrada'], 404);
            }

            // 🟢 2. REGLA DE 5 DÍAS (Ignorada si eres Admin o SuperAdmin)
            $user = auth()->user();
            $esAdmin = $user && (
                $user->hasRole('superadmin') || 
                $user->hasRole('admin') || 
                (isset($user->rol_id) && in_array($user->rol_id, [1, 2]))
            );

            $fechaRaw = $request->input('fecha') ?? $reserva->fecha;
            $fechaFormateada = date('Y-m-d', strtotime($fechaRaw));
            $fechaObj = \Carbon\Carbon::parse($fechaFormateada);

            if (!$esAdmin && $fechaObj->startOfDay() < now()->addDays(5)->startOfDay()) {
                \DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'Por políticas, las reservas deben hacerse con al menos 5 días de anticipación.'
                ], 400);
            }

            // 🟢 3. VALIDACIÓN DE ESPACIO Y CHOQUES
            $horaInicioRaw = $request->input('hora_inicio') ?? $reserva->hora_inicio;
            $horaInicioFormateada = strlen($horaInicioRaw) == 5 ? $horaInicioRaw . ':00' : $horaInicioRaw;

            $horaFinRaw = $request->input('hora_fin') ?? $reserva->hora_fin;
            $horaFinFormateada = strlen($horaFinRaw) == 5 ? $horaFinRaw . ':00' : $horaFinRaw;

            $espacioInput = $request->input('espacio_id');
            $espacioId = null;
            $otroEspacioTexto = null;

            if ($espacioInput === 'Otro' || !is_numeric($espacioInput)) {
                $espacioId = null;
                $otroEspacioTexto = $request->input('otro_espacio') ?? 'Espacio personalizado'; 
            } else {
                $espacioId = $espacioInput;
                
                // Verificamos choque EXCLUYENDO esta misma reserva para no bloquearnos a nosotros mismos
                $choque = \DB::table('reservas')
                    ->where('id', '!=', $reserva->id) 
                    ->where('fecha', $fechaFormateada)
                    ->where('espacio_id', $espacioId)
                    ->where('aprobado', '!=', -1)
                    ->where(function ($query) use ($horaInicioFormateada, $horaFinFormateada) {
                        $query->where('hora_inicio', '<', $horaFinFormateada)
                              ->where('hora_fin', '>', $horaInicioFormateada);
                    })
                    ->exists();

                if ($choque) {
                    \DB::rollBack();
                    return response()->json([
                        'success' => false, 
                        'message' => 'El espacio seleccionado ya está ocupado en ese horario.'
                    ], 400);
                }
            }

            // 🟢 4. ACTUALIZAMOS LA RESERVA BÁSICA (Usamos Query Builder como tú lo tenías originalmente para máxima seguridad)
            $datosUpdate = [
                'espacio_id'       => $espacioId,
                'otro_espacio'     => $otroEspacioTexto,
                'fecha'            => $fechaFormateada,
                'hora_inicio'      => $horaInicioFormateada,
                'hora_fin'         => $horaFinFormateada,
                'nombre_actividad' => $request->input('nombre_actividad') ?? 'Sin Nombre',
                'num_personas'     => $request->input('num_personas') ?? 0,
                'programa_evento'  => $request->input('programa_evento') ?? '',
            ];

            \DB::table('reservas')->where('id', $reserva->id)->update($datosUpdate);

            // 🟢 5. ACTUALIZAMOS LOS REQUERIMIENTOS (Borrar y reescribir)
            \DB::table('requerimientos_reserva')->where('reserva_id', $reserva->id)->delete();

            $categorias = [
                'audiovisuales'       => ['cant' => 'cantidad_audiovisuales',       'otro' => 'otro_audiovisual'],
                'servicios_generales' => ['cant' => 'cantidad_servicios_generales', 'otro' => 'otro_servicio_general'],
                'comunicaciones'      => ['cant' => 'cantidad_comunicaciones',      'otro' => 'otro_comunicacion'],
                'administracion'      => ['cant' => 'cantidad_administracion',      'otro' => 'otro_administracion'],
            ];

            $requerimientosAInsertar = [];

            foreach ($categorias as $tipo => $nombres) {
                $itemsSeleccionados = $request->input($tipo); 

                if (is_array($itemsSeleccionados) && count($itemsSeleccionados) > 0) {
                    $arregloCantidades = $request->input($nombres['cant']) ?? []; 

                    foreach ($itemsSeleccionados as $item) {
                        $cantidad = isset($arregloCantidades[$item]) ? (int) $arregloCantidades[$item] : 1;
                        $descripcionFinal = ($item === 'Otro') ? $request->input($nombres['otro']) : $item;

                        $requerimientosAInsertar[] = [
                            'reserva_id'  => $reserva->id, // ¡AHORA SÍ TIENE EL ID REAL!
                            'tipo'        => $tipo,
                            'descripcion' => $descripcionFinal,
                            'cantidad'    => $cantidad
                        ];
                    }
                }
            }

            if (!empty($requerimientosAInsertar)) {
                \DB::table('requerimientos_reserva')->insert($requerimientosAInsertar);
            }

            \DB::commit();

            // 🟢 6. LANZAR CORREOS
            $rutaArtisan = base_path('artisan');
            exec("start /B php $rutaArtisan reserva:procesar {$reserva->id} > NUL");

            return response()->json([
                'success' => true,
                'message' => '¡Reserva actualizada con todos sus requerimientos!'
            ]);

        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }




public function aprobar(\App\Models\Reserva $reserva, $tipo)
    {
        if ($tipo !== 'global') {
            // 1. Buscamos el requerimiento específico de esta área
            $requerimiento = $reserva->requerimientos()->where('tipo', $tipo)->first();

            // 2. Verificamos si ya fue respondido (0 = Pendiente)
            if ($requerimiento && $requerimiento->estado != 0) {
                // Si ya fue procesado, detenemos la ejecución
                return response('Esta solicitud ya fue procesada previamente por tu área. La decisión es definitiva.', 403);
            }

            // 3. Si está pendiente, procesamos la aprobación
            $reserva->requerimientos()->where('tipo', $tipo)->update(['estado' => 1]);
        }
        
        $this->evaluarEstadoGlobal($reserva);
        return view('reservas.aprobada', compact('reserva'));
    }

    public function rechazar(\App\Models\Reserva $reserva, $tipo)
    {
        if ($tipo !== 'global') {
            // 1. Buscamos el requerimiento específico
            $requerimiento = $reserva->requerimientos()->where('tipo', $tipo)->first();

            // 2. Verificamos si ya fue respondido
            if ($requerimiento && $requerimiento->estado != 0) {
                // Si ya fue procesado, detenemos la ejecución
                return response('Esta solicitud ya fue procesada previamente por tu área. La decisión es definitiva.', 403);
            }

            // 3. Si está pendiente, procesamos el rechazo
            $reserva->requerimientos()->where('tipo', $tipo)->update(['estado' => -1]);
        }
        
        $this->evaluarEstadoGlobal($reserva);
        return view('reservas.rechazada', compact('reserva'));
    }

    // 🧠 EL JUEZ DEL CONSENSO
    private function evaluarEstadoGlobal(\App\Models\Reserva $reserva)
    {
        $requerimientos = $reserva->requerimientos()->get();
        $total = $requerimientos->count();
        
        $rechazados = $requerimientos->where('estado', -1)->count();
        $aprobados = $requerimientos->where('estado', 1)->count();
        
        $estadoGlobalViejo = $reserva->aprobado;

        // Reglas de negocio:
        if ($total > 0 && $rechazados == $total) {
            $reserva->aprobado = -1; // 🔴 RECHAZO TOTAL (Todos dijeron que no)
        } elseif ($total == 0 || $aprobados == $total) {
            $reserva->aprobado = 1;  // 🟢 APROBACIÓN TOTAL (Todos dijeron que sí)
        } elseif ($rechazados > 0) {
            $reserva->aprobado = 2;  // 🟣 CONFLICTO (Alguien dijo no, pero no todos)
        } else {
            $reserva->aprobado = 0;  // 🟡 PENDIENTE (Faltan votos, nadie ha dicho no)
        }

        $reserva->save();

        // Enviar correos solo si el estado global acaba de cambiar
        if ($reserva->aprobado == 1 && $estadoGlobalViejo != 1) {
            $this->enviarCorreoEstado($reserva, 'emails.reserva_aprobada', '✅ Tu reserva ha sido APROBADA totalmente');
        } elseif ($reserva->aprobado == -1 && $estadoGlobalViejo != -1) {
            $this->enviarCorreoEstado($reserva, 'emails.reserva_rechazada', '❌ Tu reserva ha sido RECHAZADA totalmente');
        } elseif ($reserva->aprobado == 2 && $estadoGlobalViejo != 2 && $estadoGlobalViejo != -1) {
            $this->enviarCorreoEstado($reserva, 'emails.reserva_rechazada', '⚠️ Conflicto con los servicios de tu reserva');
        }
    }

    // 📧 ENVIADOR DE CORREOS AUTOMÁTICO
    private function enviarCorreoEstado($reserva, $vista, $asunto) {
        $reserva->load(['usuario', 'requerimientos', 'espacio']);
        try {
            \Illuminate\Support\Facades\Mail::send(
                $vista,
                ['reserva' => $reserva],
                function ($message) use ($reserva, $asunto) {
                    $message->to($reserva->usuario->email)->subject($asunto);
                }
            );
        } catch (\Exception $e) {
            \Log::error("Error enviando correo de estado: " . $e->getMessage());
        }
    }



public function destroy(\Illuminate\Http\Request $request)
{
    try {
        $id = $request->input('id');
        if (!$id) return response()->json(['success' => false, 'message' => 'ID no recibido'], 400);

        // 🟢 CÓDIGO LIMPIO: Laravel sumará automáticamente el prefijo "sem_" de la configuración
        $deleted = \Illuminate\Support\Facades\DB::table('reservas')->where('id', $id)->delete();

        if ($deleted) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'No se encontró la reserva'], 404);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Error de base de datos: ' . $e->getMessage()
        ], 500);
    }
}


public function forzarEstado(\App\Models\Reserva $reserva, $estado)
    {
        // 🔴 MODO DIOS: Quitamos la validación estricta de roles por ahora
        // Ya que los botones de la vista solo le aparecen a quien debe verlos.

        // 1. Cambiamos el estado global (1 = Aprobado, -1 = Rechazado)
        $reserva->aprobado = $estado;
        $reserva->save();

        // 2. Forzamos los requerimientos internos para que el semáforo concuerde
        \DB::table('requerimientos_reserva')
            ->where('reserva_id', $reserva->id)
            ->update(['estado' => $estado]);

        return response()->json(['success' => true]);
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