    @extends('adminlte::page')

@section('title', 'Calendario de Reservas')

@section('content_header')
    <h1>Calendario de Reservas</h1>
@stop

@section('css')
<meta name="url-events" content="{{ route('reservas.events') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Tooltip */
    .fc-event-tooltip {
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        font-size: 0.9em;
        white-space: normal;
        max-width: 300px;
        max-height: 80vh;
        overflow-y: auto;
    }

    /* 🟢 MODAL: Reserva */
    #modalReserva,
    #modalInfoReserva {
        display: none !important; /* Ocultarlos inicialmente */
    }

    #modalReserva.flex,
    #modalInfoReserva.flex {
        display: flex !important; /* Mostrar como modal flotante al agregar 'flex' con JS */
        align-items: center;
        justify-content: center;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background-color: rgba(0, 0, 0, 0.5);
        padding: 1rem;
    }

    #modalReserva > div,
    #modalInfoReserva > div {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        max-height: 90vh;
        overflow-y: auto;
        width: 100%;
        max-width: 700px;
        padding: 1.5rem;
    }

    /* AdminLTE fix para evitar que el contenido se desborde */
    .content-wrapper {
        overflow: visible !important;
        position: relative;
    }

    #modalInfoReserva {
    display: none !important;
    position: fixed !important;
    inset: 0 !important;
    z-index: 99999 !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
    justify-content: center;
    align-items: center;
}

#modalInfoReserva.show {
    display: flex !important;
}

#modalInfoReserva .modal-content {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    max-width: 600px;
    width: 90%;
}


 .hidden {
    display: none !important;
  }

  .formulario-bonito {
    background: #ffffff;
    padding: 2rem;
    border-radius: 12px;
    max-width: 700px;
    margin: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  }

  .formulario-bonito label {
    display: block;
    font-weight: 600;
    color: #1e3a8a; /* azul institucional */
    margin-bottom: 0.25rem;
  }

  .formulario-bonito input,
  .formulario-bonito select,
  .formulario-bonito textarea {
    display: block;
    width: 100%;
    padding: 0.5rem;
    margin-bottom: 1rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.375rem;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
  }

  .formulario-bonito h2 {
    text-align: center;
    color: #1e40af;
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
  }

    

    .form-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-left: 0.25rem;
    }

    .form-item input[type="checkbox"] {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .form-item label {
        flex: 1; /* El label toma el espacio del medio */
        font-weight: 500;
        color: #1e3a8a; /* azul oscuro bonito */
    }

    .form-item select {
        width: 5rem;
    }

    .hidden {
        display: none !important;
    }


</style>
@stop

@section('content')
<div class="container-fluid mt-4">
    <div class="card-body">
        <!-- Contenedor del calendario -->
        <div class="card">    
            <div id="calendar"></div>
         </div>
          </div>
           </div>
                
                <!-- modal reserva -->
   <div id="modalReserva" class="fixed inset-0 hidden items-center justify-center p-4 bg-black bg-opacity-50">
    <div 
        class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
        @click.stop
    >
                <!-- formulario paso 1 -->
                <form id="formPaso1" class="formulario-bonito space-y-6 w-full max-w-3xl mx-auto p-6 bg-white rounded-lg shadow">
        <h2 class="text-2xl font-bold text-center text-blue-900">Paso 1: Información Básica</h2>

        <!-- Espacio -->
        <div>
            <label class="block text-sm font-medium text-blue-800 mb-1">Espacio a reservar</label>
            <div class="flex gap-2">
            <select id="campoEspacio" name="espacio_id" class="w-2/3 border-gray-300 rounded-md shadow-sm focus:ring-blue-400 focus:border-blue-400">
                <option value="" selected disabled>Selecciona un espacio</option>
                @foreach ($espacios as $espacio)
                <option value="{{ $espacio->id }}">{{ $espacio->nombre }}</option>
                @endforeach
                <option value="Otro">Otro</option>
            </select>
            <input type="text" id="otroEspacio" name="otro_espacio" class="w-1/3 border-gray-300 rounded-md shadow-sm focus:ring-blue-300 focus:border-blue-300 opacity-50" placeholder="Especifique el espacio">
            </div>
        </div>

        <!-- Fecha -->
        <div>
            <label class="block text-sm font-medium text-blue-800 mb-1">Fecha</label>
            <input type="date" id="campoFecha" name="fecha" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-400 focus:border-blue-400">
        </div>

        <!-- Hora inicio -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
            <label class="block text-sm font-medium text-blue-800 mb-1">Hora inicio</label>
            <input type="time" id="campoHoraInicio" name="hora_inicio" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-400 focus:border-blue-400">
            </div>

            <!-- Hora final -->
            <div>
            <label class="block text-sm font-medium text-blue-800 mb-1">Hora final</label>
            <input type="time" id="campoHoraFinal" name="hora_fin" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-400 focus:border-blue-400">
            </div>
        </div>

        <!-- Nombre actividad -->
        <div>
            <label class="block text-sm font-medium text-blue-800 mb-1">Nombre de la actividad</label>
            <input type="text" id="campoActividad" name="nombre_actividad" placeholder="Ej: Taller de programación" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-400 focus:border-blue-400">
        </div>

        <!-- Programa evento -->
        <div>
            <label class="block text-sm font-medium text-blue-800 mb-1">Programa del evento</label>
            <textarea id="campoPrograma" name="programa_evento" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-400 focus:border-blue-400"></textarea>
        </div>

        <!-- Personas -->
        <div>
        <label class="block text-sm font-medium text-blue-800 mb-1">Número de personas esperadas</label>
        <select id="campoPersonas" name="num_personas" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-400 focus:border-blue-400">
            @for ($i = 1; $i <= 500; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </select>
    </div>

        </form>




                        <!-- 🔹 FORMULARIO PASO 2 -->
            <form id="formPaso2" class="formulario-bonito hidden">
        <h2 class="text-xl font-bold text-center mb-4">Paso 2: Requerimientos</h2>

                
            
                    
            <!-- 🔹 Sección Audiovisuales con alineación perfecta -->
                <div class="mb-6">
                <h3 class="text-lg font-semibold text-blue-700 border-b pb-1 mb-3">Audiovisuales</h3>
                <div class="space-y-2">
                    @php
                        $audiovisuales = ['Computador', 'Cámara', 'Conexión a Internet', 'Pantalla para Proyección', 'Pantalla (TV)', 'Video Bin', 'Sonido', 'Micrófono'];
                        $sinCantidad = ['Conexión a Internet', 'Pantalla para Proyección', 'Video Bin', 'Sonido'];
                    @endphp

                    @foreach ($audiovisuales as $item)
                        <div class="form-item">
                            <input type="checkbox" id="audio-{{ $loop->index }}" name="audiovisuales[]" value="{{ $item }}">
                            <label for="audio-{{ $loop->index }}">{{ $item }}</label>

                            @if (!in_array($item, $sinCantidad))
                                <select name="cantidad_audiovisuales[{{ $item }}]" class="hidden ...">
                                    @for ($i = 1; $i <= 30; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            @endif
                        </div>
                    @endforeach

                    <!-- Otro audiovisual -->
                    <div class="form-item ml-1">
                        <input type="checkbox" id="audioOtro" name="audiovisuales[]" value="Otro">
                        <label for="audioOtro">Otro</label>
                    </div>
                    <div id="otro_audiovisuales" class="hidden ml-6 space-y-2 mt-2">
                        <input type="text" name="otro_audiovisual" placeholder="Especifica otro audiovisual" class="w-full border-gray-300 rounded-md px-3 py-1 text-sm">                 
                    </div>
                </div>
            </div>





        
        <!-- 🔹 Servicios Generales -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-blue-700 border-b pb-1 mb-3">Servicios Generales</h3>
            <div class="space-y-2">
                @php
                    $servicios = ['Mesa', 'Mantel', 'Extensión eléctrica', 'Multitoma'];
                @endphp

                @foreach ($servicios as $item)
                    <div class="form-item">
                        <input type="checkbox" id="serv-{{ $loop->index }}" name="servicios_generales[]" value="{{ $item }}">
                        <label for="serv-{{ $loop->index }}">{{ $item }}</label>
                        <select name="cantidad_servicios_generales[{{ $item }}]" class="hidden border-gray-300 rounded-md px-2 py-1 text-sm shadow-sm">
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                @endforeach

                
                                <!-- Otro servicio general -->
                <div class="form-item ml-1">
                    <input type="checkbox" id="servOtro" name="servicios_generales[]" value="Otro">
                    <label for="servOtro">Otro</label>
                </div>
                <div id="otro_servicios_generales" class="hidden ml-6 space-y-2 mt-2">
                    <input type="text" name="otro_servicio_generales" placeholder="Especifica otro servicio" class="w-full border-gray-300 rounded-md px-3 py-1 text-sm">                    
                </div>

            </div>
        </div>


        
       <!-- 🔹 Comunicaciones -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-blue-700 border-b pb-1 mb-3">Comunicaciones</h3>
            <div class="space-y-2">
                @php
                    $comunicaciones = ['Fotografía', 'Video'];
                @endphp

                @foreach ($comunicaciones as $item)
                    <div class="form-item">
                        <input type="checkbox" id="com-{{ $loop->index }}" name="comunicaciones[]" value="{{ $item }}">
                        <label for="com-{{ $loop->index }}">{{ $item }}</label>
                        <select name="cantidad_comunicaciones[{{ $item }}]" class="hidden border-gray-300 rounded-md px-2 py-1 text-sm shadow-sm">
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                @endforeach

                
              <!-- Otro comunicación -->
                <div class="form-item ml-1">
                    <input type="checkbox" id="comOtro" name="comunicaciones[]" value="Otro">
                    <label for="comOtro">Otro</label>
                </div>
                <div id="otro_comunicaciones" class="hidden ml-6 space-y-2 mt-2">
                    <input type="text" name="otro_comunicacion" placeholder="Especifica otra comunicación" class="w-full border-gray-300 rounded-md px-3 py-1 text-sm">
                </div>

            </div>
        </div>

        
            <!-- 🔹 Administración -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-blue-700 border-b pb-1 mb-3">Administración</h3>
            <div class="space-y-2">
                @php
                    $administracion = ['Refrigerio', 'Agua', 'Vasos'];
                @endphp

                @foreach ($administracion as $item)
                    <div class="form-item">
                        <input type="checkbox" id="admin-{{ $loop->index }}" name="administracion[]" value="{{ $item }}">
                        <label for="admin-{{ $loop->index }}">{{ $item }}</label>
                        <select name="cantidad_administracion[{{ $item }}]" class="hidden border-gray-300 rounded-md px-2 py-1 text-sm shadow-sm">
                            @for ($i = 1; $i <= 100; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                @endforeach

                
               <!-- Otro administración -->
                <div class="form-item ml-1">
                    <input type="checkbox" id="adminOtro" name="administracion[]" value="Otro">
                    <label for="adminOtro">Otro</label>
                </div>
                <div id="otro_administracion" class="hidden ml-6 space-y-2 mt-2">
                    <input type="text" name="otro_administracion" placeholder="Especifica otro recurso" class="w-full border-gray-300 rounded-md px-3 py-1 text-sm">                  
                </div>

            </div>
        </div>

        </form>




        <!-- 🔹 BOTONES DE NAVEGACIÓN -->
        <div class="flex justify-between px-6 py-4 border-t bg-gray-50">
            <button type="button" id="btnAnterior" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded hidden">
                Anterior
            </button>
            <button type="button" id="btnSiguiente" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Siguiente
            </button>
            <button type="submit" id="btnEnviar" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded hidden">
                Reservar
            </button>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>

    <div id="modalInfoReserva" class="fixed inset-0 hidden items-center justify-center z-50 bg-black bg-opacity-50">

        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
            <!-- Botón de cerrar -->
            <button id="btnCerrarInfoReserva" class="absolute top-2 right-2 text-gray-600 hover:text-red-600 text-2xl leading-none">&times;</button>


            <!-- Contenedor donde se cargan los detalles de la reserva -->
            <div id="reservaInfoContent" class="text-gray-800 space-y-2">
                <p>Cargando...</p>
            </div>

        
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="{{ asset('js/calendario.js') }}"></script>

@endsection





