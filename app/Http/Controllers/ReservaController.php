<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Espacio;
use Illuminate\Support\Facades\DB;
use App\Models\RequerimientoReserva;

// correo
use App\Events\ReservaCreada;

class ReservaController extends Controller
{
    /**
     * Guarda una nueva reserva en la base de datos.
     */
    public function store(Request $request)
    {
        $reserva = Reserva::create([
            'usuario_id' => Auth::id(),
            'espacio_id' => $request->input('espacio_id'),
            'fecha' => $request->input('fecha'),
            'hora_inicio' => $request->input('hora_inicio'),
            'hora_fin' => $request->input('hora_fin'),
            'nombre_actividad' => $request->input('nombre_actividad'),
            'num_personas' => $request->input('num_personas'),
            'programa_evento' => $request->input('programa_evento'),
            'aprobado' => 0, // Establecer por defecto como no aprobado
        ]);

        if ($request->has('administracion')) {
            foreach ($request->input('administracion') as $item) {
                RequerimientoReserva::create([
                    'reserva_id' => $reserva->id,
                    'tipo' => 'administracion',
                    'descripcion' => $item,
                    'cantidad' => $request->input("cantidad_administracion.{$item}"),
                ]);
            }
        }
        // event(new ReservaCreada($reserva)); // Dispara el evento (comentado temporalmente)
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
    public function getReservations()
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
    public function getEvents()
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
     * Guarda los requerimientos asociados a una reserva (función privada).
     */
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
        $this->authorize('update', $reserva);
        $espacios = $this->getEspacios();
        return view('reservas.edit', compact('reserva', 'espacios'));
    }

    /**
     * Muestra los detalles de una reserva.
     */
    public function show(Reserva $reserva)
    {
        $user = Auth::user();

        if ($user && $this->authorize('view', $reserva)) {
            // Si el usuario está autenticado y tiene permiso para ver esta reserva
            return response()->json($reserva->load(['usuario', 'espacio', 'requerimientos']));
        }

        // Si el usuario no está autenticado o no tiene permiso
        abort(403, 'No tienes permisos para ver esta reserva.');
    }
  
    public function update(Request $request, Reserva $reserva)
    {
        $this->authorize('update', $reserva); // Re-verificamos la autorización antes de actualizar

        $request->validate([
            'espacio_id' => 'required|exists:espacios,id', // Asegúrate de que 'espacio_id' exista en la tabla 'espacios'
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'nombre_actividad' => 'required|string|max:255',
            'num_personas' => 'nullable|integer|min:1',
            'programa_evento' => 'nullable|string',
            // Puedes añadir validaciones para 'administracion' y 'cantidad_administracion' si es necesario
        ]);

        $reserva->update($request->all());

        // Eliminar y guardar los requerimientos actualizados
        DB::table('requerimientos_reserva')->where('reserva_id', $reserva->id)->delete();
        if ($request->has('administracion')) {
            foreach ($request->input('administracion') as $item) {
                RequerimientoReserva::create([
                    'reserva_id' => $reserva->id,
                    'tipo' => 'administracion',
                    'descripcion' => $item,
                    'cantidad' => $request->input("cantidad_administracion.{$item}"),
                ]);
            }
        }

        return redirect()->route('reservas.index')->with('success', 'Reserva actualizada correctamente.');
    }
    
     //Elimina una reserva.
     
    public function destroy(Reserva $reserva)
    {
        $this->authorize('delete', $reserva);
        $reserva->delete();
        return redirect()->route('reservas.index')->with('success', 'Reserva eliminada correctamente.');
    }

    }





