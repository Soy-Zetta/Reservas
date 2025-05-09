<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Espacio;
use Illuminate\Support\Facades\DB;
use App\Models\RequerimientoReserva;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReservaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Restringe el acceso a usuarios autenticados.
     */
    public function __construct()
    {
     

        /**
         * Restringe el acceso a usuarios autenticados
         */
     //   $this->middleware('auth'); // Esto aplica el middleware 'auth' a las rutas que usan este controlador
    }

    /**
     * Muestra el formulario de creación de reservas.
     */
    public function create()
    {
        $espacios = Espacio::all(); // Cargar todos los espacios disponibles desde la base de datos
        return view('calendario', compact('espacios'));
    }

    /**
     * Devuelve las reservas en formato JSON para FullCalendar.
     */
    public function getReservations()
    {
        $reservas = Reserva::where('user_id', Auth::id())->get();
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
        return view('reservas.calendario', compact('espacios'));
    }

    /**
     * Devuelve todos los eventos en formato JSON.
     */
    public function obtenerEventos()
    {
        $reservas = Reserva::all();
        $eventos = [];

        foreach ($reservas as $reserva) {
            $eventos[] = [
                'title' => $reserva->nombre_actividad,
                'start' => "{$reserva->fecha}T{$reserva->hora_inicio}",
                'end' => "{$reserva->fecha}T{$reserva->hora_fin}",
            ];
        }

        return response()->json($eventos);
    }

    /**
     * Guarda una nueva reserva en la base de datos.
     */
    public function store(Request $request)
    {
        // Validación de los datos del formulario
        $request->validate([
            'espacio_id' => 'required_without:otro_espacio|string',
            'otro_espacio' => 'nullable|required_if:espacio_id,Otro|string',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'nombre_actividad' => 'required|string|max:255',
            'num_personas' => 'nullable|integer|min:1',
            'programa_evento' => 'nullable|string',
            'audiovisuales' => 'nullable|array',
            'otro_audiovisual' => 'nullable|string',
            'cantidad_audiovisuales' => 'nullable|array', // Nueva validación para las cantidades
            'servicios_generales' => 'nullable|array',
            'otro_servicio_general' => 'nullable|string',
            'cantidad_servicios_generales' => 'nullable|array', // Nueva validación para las cantidades
            'comunicaciones' => 'nullable|array',
            'otro_comunicacion' => 'nullable|string',
            'cantidad_comunicaciones' => 'nullable|array', // Nueva validación para las cantidades
            'administracion' => 'nullable|array',
            'otro_administracion' => 'nullable|string',
            'cantidad_administracion' => 'nullable|array', // Nueva validación para las cantidades
        ]);
    
        // Crear una nueva instancia de Reserva y asignar los valores
        $reserva = new Reserva();
        $reserva->usuario_id = Auth::check() ? Auth::id() : null;
    
        if ($request->espacio_id !== 'Otro') {
            $espacio = Espacio::find($request->espacio_id);
            if ($espacio) {
                $reserva->espacio_id = $espacio->id;
                $reserva->espacio_nombre = null;
            } else {
                return back()->withErrors(['error' => 'El espacio seleccionado no es válido.'])->withInput();
            }
        } else {
            $reserva->espacio_id = null;
            $reserva->espacio_nombre = $request->otro_espacio;
        }
    
        $reserva->fecha = $request->fecha;
        $reserva->hora_inicio = $request->hora_inicio;
        $reserva->hora_fin = $request->hora_fin;
        $reserva->nombre_actividad = $request->nombre_actividad;
        $reserva->num_personas = $request->num_personas;
        $reserva->programa_evento = $request->programa_evento;
    
        $reserva->save();
    
        // Guardar los requerimientos en la tabla requerimientos_reserva con cantidad
        $this->guardarRequerimientos($request, $reserva->id, 'audiovisuales', 'otro_audiovisual', 'cantidad_audiovisuales');
        $this->guardarRequerimientos($request, $reserva->id, 'servicios_generales', 'otro_servicio_general', 'cantidad_servicios_generales');
        $this->guardarRequerimientos($request, $reserva->id, 'comunicaciones', 'otro_comunicacion', 'cantidad_comunicaciones');
        $this->guardarRequerimientos($request, $reserva->id, 'administracion', 'otro_administracion', 'cantidad_administracion');
    
        return redirect()->route('reservas.index')->with('success', 'Reserva creada correctamente.');
    }

    private function guardarRequerimientos(Request $request, int $reservaId, string $categoria, string $otroCampo, string $cantidadCampo)
    {
        if ($request->has($categoria)) {
            foreach ($request->input($categoria) as $requerimiento) {
                $descripcion = $requerimiento === 'Otro' && $request->has($otroCampo) ? $request->input($otroCampo) : $requerimiento;
                $cantidad = $request->has($cantidadCampo) && isset($request->input($cantidadCampo)[$requerimiento]) ? $request->input($cantidadCampo)[$requerimiento] : null;

                DB::table('requerimientos_reserva')->insert([
                    'reserva_id' => $reservaId,
                    'tipo' => $categoria,
                    'descripcion' => $descripcion,
                    'cantidad' => $cantidad,
                ]);
            }
        }
    }

    /**
     * Muestra el formulario de edición de una reserva.
     */
    public function edit(Reserva $reserva)
    {
        $this->authorize('manage-all-reservations');
        if ($reserva->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permisos para editar esta reserva.');
        }

        $espacios = $this->getEspacios();
        return view('reservas.edit', compact('reserva', 'espacios'));
    }

    public function show(Reserva $reserva)
    {
        $user = Auth::user();
        if ($user && ($user->can('manage-all-reservations') || $user->id === $reserva->usuario_id)) {
            // Si el usuario está autenticado Y tiene permiso (es Admin/Superadmin O es el dueño de la reserva)
            // Continuar con la lógica para cargar las relaciones y devolver los datos de la reservas
            return response()->json($reserva->load(['usuario', 'espacio', 'requerimientos']));
        }

        // Si el usuario no está autenticado o no tiene permiso
        // Detener la ejecución y mostrar un error 403 Forbidden (Acceso Denegado)
        abort(403, 'No tienes permisos para ver esta reserva.');
    }
        
    

    /**
     * Actualiza una reserva en la base de datos.
     */
    public function update(Request $request, Reserva $reserva)
    {
        if ($reserva->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permisos para actualizar esta reserva.');
        }

        $request->validate([
            'espacio' => 'required|string',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'nombre_actividad' => 'required|string|max:255',
        ]);

        $reserva->update($request->all());

        return redirect()->route('reservas.index')->with('success', 'Reserva actualizada correctamente.');
    }

    /**
     * Elimina una reserva.
     */
    public function destroy(Reserva $reserva)
    {
        $this->authorize('manage-all-reservations');
        // --- Verificar Permiso con Gate ---
    
        $reserva->delete();

        return redirect()->route('reservas.index')->with('success', 'Reserva eliminada correctamente.');
    }

    /**
     * Devuelve una lista de los espacios disponibles.
     */
    private function getEspacios()
    {
        return [
            'Auditorio Tecnológico 1',
            'Auditorio Tecnológico 2',
            'Auditorio Tecnológico 3',
            'Auditorio Audiovisual 4',
            'Sala Informatica 1',
            'Sala Informatica 2',
            'Sala Informatica 3',
            'Sala - Juntas',
            'Capilla - auditorio',
            'Biblioteca - Infantil',
            'Biblioteca - Bachillerato',
            'Biblioteca - Sala de lectura',
            'Coliseo - Espacios deportivos',
            'Laboratorio de física',
            'Laboratorio de química',
            'Emisora Colamer',
            'Otro'
        ];
    }

    /**
     * Devuelve los eventos en formato JSON.
     */
    public function getEvents(Request $request)
    {
        $reservas = Reserva::with('espacio')->get();
    
        $events = [];
    
        foreach ($reservas as $reserva) {
            $events[] = [
                'id' => $reserva->id,
                'title' => $reserva->nombre_actividad,
                'start' => $reserva->fecha . 'T' . $reserva->hora_inicio,
                'end' => $reserva->fecha . 'T' . $reserva->hora_fin,
                'extendedProps' => [
                    'usuario' => $reserva->user->name ?? 'No disponible', // Asumiendo que tienes relación con el modelo User
                    'num_personas' => $reserva->num_personas,
                    'espacio' => $reserva->espacio ? $reserva->espacio->nombre : null,
                    'espacio_nombre' => $reserva->otro_espacio,
                ],
            ];
        }
    
        return response()->json($events);
    }

    public function index()
    {
        $reservas = Reserva::all();

        $events = [];

        foreach ($reservas as $reserva) {
            $events[] = [
                'title' => $reserva->nombre_actividad,
                'start' => $reserva->fecha . 'T' . $reserva->hora_inicio,
                'end'   => $reserva->fecha . 'T' . $reserva->hora_fin,
            ];
        }

        return response()->json($events);
    }
}
