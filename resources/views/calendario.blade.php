@extends('adminlte::page')

@section('title', 'Calendario de Reservas')

@section('content_header')
    <h1>Calendario de Reservas</h1>
@stop

@section('css')
<meta name="url-events" content="{{ route('reservas.events') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="is-admin" content="{{ auth()->check() && auth()->user()->isAdmin() ? '1' : '0' }}">
<meta name="is-superadmin" content="{{ auth()->check() && auth()->user()->hasRole('superadmin') ? '1' : '0' }}">
<meta name="url-eliminar" content="{{ route('reservas.destroy.final') }}">

<style>
    /* 🟢 RECUPERADOS: Estilos de botones originales */
    #btnEditarReserva, #btnEliminarReserva {
        padding: 0.5rem 1rem !important; border-radius: 0.25rem !important; font-weight: bold !important;
        border: none !important; cursor: pointer !important; transition: background-color 0.3s !important;
        font-size: 0.875rem !important; margin-left: 0.5rem !important;
    }
    #btnEditarReserva { background-color: #facc15 !important; color: #000 !important; }
    #btnEditarReserva:hover { background-color: #eab308 !important; }
    #btnEliminarReserva { background-color: #ef4444 !important; color: #fff !important; }
    #btnEliminarReserva:hover { background-color: #dc2626 !important; }
    
    #btnSiguiente, #btnEnviar, #btnReservar {
        background-color: #3b82f6 !important; color: white !important; font-weight: bold !important;
        padding: 0.5rem 1rem !important; border-radius: 0.25rem !important; border: none !important; cursor: pointer !important;
    }
    #btnEnviar { background-color: #10b981 !important; }
    #btnEnviar:hover { background-color: #059669 !important; }
    #btnAnterior { background-color: #9ca3af !important; color: #111827 !important; }
    #btnAnterior:hover { background-color: #6b7280 !important; }
    #btnSiguiente:hover { background-color: #2563eb !important; }

    /* 🟢 RECUPERADO: Tooltip del calendario */
    .fc-event-tooltip {
        background-color: #f9f9f9; border: 1px solid #ccc; padding: 10px; border-radius: 5px;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2); font-size: 0.9em; white-space: normal;
        max-width: 300px; max-height: 80vh; overflow-y: auto;
    }

    /* 🟢 RECUPERADO: Fix vital de AdminLTE */
    .content-wrapper { overflow: visible !important; position: relative; }

   /* 🟢 MODAL: Comportamiento de visualización */
 #modalReserva.flex, #modalInfoReserva.show {
        display: flex !important; align-items: center; justify-content: center;
        position: fixed; inset: 0; z-index: 99999; padding: 1rem;
        background-color: transparent !important; /* Quita el negro */
        backdrop-filter: blur(4px); /* Agrega un desenfoque elegante al fondo */
    }
    
    /* MODAL INFO RESERVA: Centrado estricto */
    #modalInfoReserva { display: none !important; position: fixed; inset: 0; z-index: 99999; }
    #modalInfoReserva.show { display: block !important; }

    /* Efecto hover elegante para el botón de cierre */
    #btnCerrarInfoReserva:hover { background-color: #f8d7da !important; transform: scale(1.1); }
    #btnCerrarInfoReserva:focus { outline: none; box-shadow: none; }
    
    .hidden { display: none !important; }
    
    .form-item { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; }
    .form-item input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
    .form-item label { flex: 1; margin-bottom: 0; font-weight: 500; color: #343a40; cursor: pointer; }
    .form-item select { width: 70px; padding: 0.1rem 0.5rem; }



/* 🟢 FIX VITAL: Forzar texto oscuro en la ventana de detalles */
    #reservaInfoContent,
    #reservaInfoContent p,
    #reservaInfoContent li,
    #reservaInfoContent strong,
    #reservaInfoContent span,
    #reservaInfoContent div {
        color: #343a40 !important; /* Negro oscuro elegante de Bootstrap */
    }
    
    #reservaInfoContent .list-group-item {
        background-color: transparent !important; /* Evita fondos blancos ocultos */
        border-color: #e9ecef !important; /* Líneas divisorias suaves */
    }



/* 🟢 ESTILOS PREMIUM PARA FORMULARIO */
    .label-premium {
        font-size: 0.85rem;
        color: #6c757d; /* Gris sutil */
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .input-premium {
        border-radius: 0.5rem !important; /* Bordes más redondeados */
        padding: 0.6rem 1rem !important; /* Más espacio interno */
        border: 1px solid #ced4da;
        box-shadow: inset 0 1px 2px rgba(0,0,0,.04);
        background-color: #fcfcfc;
    }

    .input-premium:focus {
        background-color: #ffffff;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.15) !important;
    }


/* 🟢 FORZAR SWEETALERT AL FRENTE DE TODO */
    div.swal2-container {
        z-index: 999999 !important;
    }

</style>
@stop

@section('content')

    <div class="card shadow-sm border-0">
        <div class="card-body p-3">
            <div id="calendar"></div>
        </div>
    </div>

    <div id="modalReserva" class="fixed inset-0 hidden z-50">
        <div class="d-flex align-items-center justify-content-center w-100 h-100 p-3">
            
            <div class="bg-white rounded-lg shadow-xl w-full" style="max-width: 850px; max-height: 90vh; display: flex; flex-direction: column;" @click.stop>
                
             <div class="p-3 border-bottom bg-light rounded-top position-relative d-flex align-items-center" style="flex-shrink: 0;">
                    <h4 class="text-primary font-weight-bold m-0 text-center w-100">
                        <i class="fas fa-calendar-alt mr-2"></i> Formulario de Reserva
                    </h4>
                    <button type="button" id="btnCerrarFormulario" class="btn btn-sm btn-light rounded-circle shadow-sm position-absolute" style="right: 15px; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; z-index: 10;">
                        <i class="fas fa-times text-danger fa-lg"></i>
                    </button>
                </div>

                <div class="p-4" style="overflow-y: auto; flex-grow: 1;">
                    
                    <form id="formPaso1" class="w-100">
                        <h5 class="text-secondary border-bottom pb-2 mb-4"><i class="fas fa-info-circle mr-2"></i>Paso 1: Información Básica</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="label-premium">Espacio a reservar</label>
                                <select id="campoEspacio" name="espacio_id" class="form-control input-premium">
                                    <option value="" selected disabled>Selecciona un espacio</option>
                                    @foreach ($espacios as $espacio)
                                        <option value="{{ $espacio->id }}">{{ $espacio->nombre }}</option>
                                    @endforeach
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="label-premium">Especifique (Si eligió "Otro")</label>
                                <input type="text" id="otroEspacio" name="otro_espacio" class="form-control input-premium opacity-50" placeholder="Especifique el espacio" disabled>
                            </div>
                        </div>

                        <div class="row">
                          <div class="col-md-4 mb-4">
                                <label class="label-premium">Fecha</label>
                                <input type="date" id="campoFecha" name="fecha" class="form-control input-premium bg-light text-muted cursor-not-allowed" readonly>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="label-premium">Hora inicio</label>
                                <input type="time" id="campoHoraInicio" name="hora_inicio" class="form-control input-premium">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="label-premium">Hora final</label>
                                <input type="time" id="campoHoraFinal" name="hora_fin" class="form-control input-premium">
                            </div>
                        </div>

                     <div class="row">
                            <div class="col-md-8 mb-4">
                                <label class="label-premium">Nombre de la actividad</label>
                                <input type="text" id="campoActividad" name="nombre_actividad" placeholder="Ej: Taller de programación" class="form-control input-premium">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="label-premium">N° de personas</label>
                                <select id="campoPersonas" name="num_personas" class="form-control input-premium">
                                    <option value="" selected disabled>Cantidad...</option>
                                    @for ($i = 1; $i <= 500; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                                <!-- 🟢 NUEVO CHECKBOX: SIN PERSONAS -->
                                <div class="form-check mt-2 pl-1">
                                    <input class="form-check-input ml-0 mr-2" type="checkbox" id="checkSinPersonas" value="1" style="position: relative;">
                                    <label class="form-check-label text-muted" style="font-size: 0.85rem; cursor: pointer;" for="checkSinPersonas">
                                        Sin especificar / Sin personas
                                    </label>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label class="label-premium">Programa del evento</label>
                                <textarea id="campoPrograma" name="programa_evento" rows="3" class="form-control input-premium" placeholder="Describa brevemente el programa..."></textarea>
                            </div>
                        </div>
                    </form>

                    <form id="formPaso2" class="hidden w-100">
                        <h5 class="text-secondary border-bottom pb-2 mb-3"><i class="fas fa-clipboard-list mr-2"></i>Paso 2: Requerimientos</h5>
                        
                        <!-- 🟢 NUEVO CHECKBOX: SIN REQUERIMIENTOS -->
                        <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm" role="alert" style="border-left: 5px solid #facc15; background-color: #fffbeb;">
                            <input class="form-check-input ml-2" type="checkbox" id="checkSinRequerimientos" value="1" style="transform: scale(1.5); cursor: pointer; margin-top: 0;">
                            <label class="form-check-label ml-4 font-weight-bold text-dark" for="checkSinRequerimientos" style="cursor: pointer; padding-left: 10px;">
                                Confirmar reserva SIN ningún requerimiento adicional.
                            </label>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-primary shadow-sm mb-3">
                                    <div class="card-header p-2">
                                        <h3 class="card-title font-weight-bold text-primary"><i class="fas fa-desktop mr-1"></i> Audiovisuales</h3>
                                    </div>
                                    <div class="card-body p-2">
                                        @php
                                            $audiovisuales = ['Computador', 'Conexión a Internet', 'Pantalla para Proyección', 'Pantalla (TV)', 'Video Bin', 'Sonido', 'Micrófono Alámbrico', 'Micrófono Inalámbrico'];
                                            $sinCantidad = ['Conexión a Internet', 'Computador', 'Pantalla para Proyección', 'Video Bin', 'Sonido'];
                                        @endphp
                                        @foreach ($audiovisuales as $item)
                                            <div class="form-item">
                                                <input type="checkbox" id="audio-{{ $loop->index }}" name="audiovisuales[]" value="{{ $item }}">
                                                <label for="audio-{{ $loop->index }}">{{ $item }}</label>
                                                @if (!in_array($item, $sinCantidad))
                                                    <select name="cantidad_audiovisuales[{{ $item }}]" class="hidden form-control form-control-sm">
                                                        @for ($i = 1; $i <= 30; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                                    </select>
                                                @endif
                                            </div>
                                        @endforeach
                                        <div class="form-item">
                                            <input type="checkbox" id="audioOtro" name="audiovisuales[]" value="Otro">
                                            <label for="audioOtro">Otro</label>
                                        </div>
                                        <div id="otro_audiovisuales" class="hidden mt-1 pl-4">
                                            <input type="text" name="otro_audiovisual" placeholder="Especificar..." class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>

                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header p-2">
                                        <h3 class="card-title font-weight-bold text-info"><i class="fas fa-camera mr-1"></i> Comunicaciones</h3>
                                    </div>
                                    <div class="card-body p-2">
                                        @php $comunicaciones = ['Fotografía', 'Video']; @endphp
                                        @foreach ($comunicaciones as $item)
                                            <div class="form-item">
                                                <input type="checkbox" id="com-{{ $loop->index }}" name="comunicaciones[]" value="{{ $item }}">
                                                <label for="com-{{ $loop->index }}">{{ $item }}</label>
                                                <select name="cantidad_comunicaciones[{{ $item }}]" class="hidden form-control form-control-sm">
                                                    @for ($i = 1; $i <= 20; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                                </select>
                                            </div>
                                        @endforeach
                                        <div class="form-item">
                                            <input type="checkbox" id="comOtro" name="comunicaciones[]" value="Otro">
                                            <label for="comOtro">Otro</label>
                                        </div>
                                        <div id="otro_comunicaciones" class="hidden mt-1 pl-4">
                                            <input type="text" name="otro_comunicacion" placeholder="Especificar..." class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-outline card-success shadow-sm mb-3">
                                    <div class="card-header p-2">
                                        <h3 class="card-title font-weight-bold text-success"><i class="fas fa-chair mr-1"></i> Servicios Generales</h3>
                                    </div>
                                    <div class="card-body p-2">
                                        @php $servicios = ['Mesa','Silla', 'Mantel', 'Extensión eléctrica', 'Multitoma']; @endphp
                                        @foreach ($servicios as $item)
                                            <div class="form-item">
                                                <input type="checkbox" id="serv-{{ $loop->index }}" name="servicios_generales[]" value="{{ $item }}">
                                                <label for="serv-{{ $loop->index }}">{{ $item }}</label>
                                                <select name="cantidad_servicios_generales[{{ $item }}]" class="hidden form-control form-control-sm">
                                                    @for ($i = 1; $i <= 20; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                                </select>
                                            </div>
                                        @endforeach
                                        <div class="form-item">
                                            <input type="checkbox" id="servOtro" name="servicios_generales[]" value="Otro">
                                            <label for="servOtro">Otro</label>
                                        </div>
                                        <div id="otro_servicios_generales" class="hidden mt-1 pl-4">
                                            <input type="text" name="otro_servicio_generales" placeholder="Especificar..." class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>

                                <div class="card card-outline card-warning shadow-sm mb-3">
                                    <div class="card-header p-2">
                                        <h3 class="card-title font-weight-bold text-warning"><i class="fas fa-coffee mr-1"></i> Administración</h3>
                                    </div>
                                    <div class="card-body p-2">
                                        @php $administracion = ['Refrigerio', 'Agua', 'Vasos']; @endphp
                                        @foreach ($administracion as $item)
                                            <div class="form-item">
                                                <input type="checkbox" id="admin-{{ $loop->index }}" name="administracion[]" value="{{ $item }}">
                                                <label for="admin-{{ $loop->index }}">{{ $item }}</label>
                                                <select name="cantidad_administracion[{{ $item }}]" class="hidden form-control form-control-sm">
                                                    @for ($i = 1; $i <= 100; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                                </select>
                                            </div>
                                        @endforeach
                                        <div class="form-item">
                                            <input type="checkbox" id="adminOtro" name="administracion[]" value="Otro">
                                            <label for="adminOtro">Otro</label>
                                        </div>
                                        <div id="otro_administracion" class="hidden mt-1 pl-4">
                                            <input type="text" name="otro_administracion" placeholder="Especificar..." class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
                        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light rounded-bottom" style="flex-shrink: 0;">
            <button type="button" id="btnAnterior" class="btn btn-secondary font-weight-bold hidden"><i class="fas fa-arrow-left mr-1"></i> Anterior</button>
            
        <!-- Nuevo checkbox oculto por defecto -->
        <div id="contenedorSinRequerimientos" class="hidden mr-3 form-check" style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 5px 10px 5px 30px; border-radius: 5px;">
            <input class="form-check-input" type="checkbox" id="checkSinRequerimientos" style="margin-top: 6px;">
            <label class="form-check-label text-danger font-weight-bold" for="checkSinRequerimientos" style="cursor: pointer;">
                Confirmar reserva SIN requerimientos
            </label>
        </div>
                <button type="button" id="btnSiguiente" class="btn btn-primary font-weight-bold">Siguiente <i class="fas fa-arrow-right ml-1"></i></button>
                <button type="submit" id="btnEnviar" class="btn btn-success font-weight-bold hidden"><i class="fas fa-save mr-1"></i> Reservar</button>
            </div>
        </div>

            </div>
        </div>
    </div>

<div id="modalInfoReserva" class="fixed inset-0 hidden z-50 bg-black bg-opacity-50">
        <div class="d-flex align-items-center justify-content-center w-100 h-100 p-3">
            
            <div class="modal-content shadow-lg border-0 rounded-lg" style="max-width: 650px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;">
                
                <div class="modal-header bg-light border-bottom px-4 py-3 align-items-center">
                    <h5 class="modal-title font-weight-bold text-secondary m-0">
                        <i class="fas fa-list-alt mr-2 text-primary"></i> Detalles de la Reserva
                    </h5>
                    <button type="button" id="btnCerrarInfoReserva" class="btn btn-sm btn-light rounded-circle shadow-sm" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                        <i class="fas fa-times text-danger fa-lg"></i>
                    </button>
                </div>
                
                <div class="modal-body p-4" style="overflow-y: auto;">
                    <div id="reservaInfoContent" class="text-gray-800" style="font-size: 1.05rem;">
                        <p class="text-center text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando detalles...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop

@section('js')



<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    window.appUserRole = "{{ Auth::user()->getRoleNames()->first() ?? 'user' }}";
</script>

<script src="{{ asset('js/calendario.js') }}?v={{ time() }}"></script>


<script>
    window.appUserRole = "{{ Auth::user()->getRoleNames()->first() ?? 'user' }}";
    window.appUserId = "{{ Auth::id() }}"; // 🟢 NUEVO: Le pasamos el ID a JavaScript
</script>


<script>
document.addEventListener('DOMContentLoaded', function() { // 🟢 AQUÍ FALTABA ESTA LLAVE '{'
    const campoEspacio = document.getElementById('campoEspacio');
    const otroEspacio = document.getElementById('otroEspacio');

    if (campoEspacio && otroEspacio) {
        campoEspacio.addEventListener('change', function() {
            if (this.value === 'Otro') {
                otroEspacio.disabled = false;
                otroEspacio.classList.remove('opacity-50');
                otroEspacio.focus();
            } else {
                otroEspacio.disabled = true;
                otroEspacio.classList.add('opacity-50');
                otroEspacio.value = '';
            }
        });

        if (campoEspacio.value === 'Otro') {
            otroEspacio.disabled = false;
            otroEspacio.classList.remove('opacity-50');
        } else {
            otroEspacio.disabled = true;
            otroEspacio.classList.add('opacity-50');
        }
    } 
    

    // 🟢 INTERRUPTOR INTELIGENTE: REQUERIMIENTOS (PASO 2)
    const checkSinReq = document.getElementById('checkSinRequerimientos');
    const todosLosCheckboxesPaso2 = document.querySelectorAll('#formPaso2 input[type="checkbox"]');

    if (checkSinReq) {
        // 1. ¿Qué pasa si marca "Sin requerimientos"?
        checkSinReq.addEventListener('change', function() {
            if (this.checked) {
                // Desmarcamos todos los demás
                todosLosCheckboxesPaso2.forEach(cb => {
                    if (cb.id !== 'checkSinRequerimientos') {
                        cb.checked = false;
                        cb.dispatchEvent(new Event('change')); // Avisamos al sistema del cambio
                    }
                });
            }
        });

        // 2. ¿Qué pasa si marca algún requerimiento específico?
        todosLosCheckboxesPaso2.forEach(cb => {
            if (cb.id !== 'checkSinRequerimientos') {
                cb.addEventListener('change', function() {
                    if (this.checked) {
                        // Desmarcamos la casilla de "Sin requerimientos"
                        checkSinReq.checked = false;
                    }
                });
            }
        });


// 🟢 INTERRUPTOR INTELIGENTE: CANTIDAD DE PERSONAS (PASO 1)
    const checkSinPersonas = document.getElementById('checkSinPersonas');
    const campoPersonas = document.getElementById('campoPersonas');

    if (checkSinPersonas && campoPersonas) {
        // 1. ¿Qué pasa si marca la casilla "Sin especificar"?
        checkSinPersonas.addEventListener('change', function() {
            if (this.checked) {
                // Reiniciamos la lista desplegable a su opción por defecto (vacía)
                campoPersonas.value = '';
            }
        });

        // 2. ¿Qué pasa si selecciona un número de la lista?
        campoPersonas.addEventListener('change', function() {
            if (this.value !== '') {
                // Desmarcamos la casilla de "Sin especificar"
                checkSinPersonas.checked = false;
            }
        });
    }


    }


    // 🟢 FORZAR APERTURA DEL RELOJ CON SENSORES DE DIAGNÓSTICO
    const camposFormulario = ['campoHoraInicio', 'campoHoraFinal'];
    
    camposFormulario.forEach(id => {
        const input = document.getElementById(id);
        
        // 1. Verificar si el campo realmente existe cuando carga el código
        if (!input) {
            console.error(`❌ ATENCIÓN: El campo con ID '${id}' no se encontró en la pantalla.`);
            return; // Detiene la ejecución para este campo específico
        }

        // 2. Agregar el evento de clic con manejo de errores visible
        input.addEventListener('click', function(event) {
            console.log(`✅ Clic detectado con éxito en: ${id}`);
            
            try {
                // 3. Verificar si el navegador moderno soporta la función nativa
                if (typeof this.showPicker === 'function') {
                    this.showPicker();
                } else {
                    console.warn(`⚠️ El navegador actual no soporta la función showPicker() para el campo ${id}.`);
                }
            } catch (error) {
                // 4. Mostrar exactamente por qué falló (ej. falta de permisos o problemas de interacción)
                console.error(`❌ Error bloqueando el reloj en ${id}:`, error.message);
            }
        });
    });

    // 🟢 LÓGICA DE NAVEGACIÓN DEL FORMULARIO (PASO 1 Y PASO 2)
    const formPaso1 = document.getElementById('formPaso1');
    const formPaso2 = document.getElementById('formPaso2');
    const btnSiguiente = document.getElementById('btnSiguiente');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnEnviar = document.getElementById('btnEnviar');

    // Función maestra que cambia las pantallas
    window.mostrarPaso = function(paso) {
        if (paso === 1) {
            // Mostrar Paso 1
            formPaso1?.classList.remove('hidden');
            formPaso2?.classList.add('hidden');
            
            // Botones: Solo mostrar Siguiente
            btnAnterior?.classList.add('hidden');
            btnSiguiente?.classList.remove('hidden');
            btnEnviar?.classList.add('hidden');
        } else if (paso === 2) {
            // Mostrar Paso 2
            formPaso1?.classList.add('hidden');
            formPaso2?.classList.remove('hidden');
            
            // Botones: Mostrar Anterior y Reservar/Actualizar
            btnAnterior?.classList.remove('hidden');
            btnSiguiente?.classList.add('hidden');
            btnEnviar?.classList.remove('hidden');
        }
    };

    // Al hacer clic en Siguiente -> ir al Paso 2
    if (btnSiguiente) {
        btnSiguiente.addEventListener('click', function(e) {
            e.preventDefault();
            window.mostrarPaso(2);
        });
    }

    // Al hacer clic en Anterior -> regresar al Paso 1
    if (btnAnterior) {
        btnAnterior.addEventListener('click', function(e) {
            e.preventDefault();
            window.mostrarPaso(1);
        });
    }

}); // 🟢 AQUÍ FALTABA CERRAR LA LLAVE Y EL PARÉNTESIS DEL DOMContentLoaded
</script>
@stop