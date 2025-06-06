@extends('adminlte::page')

@section('title', 'Calendario de Reservas')


@section('content_header')
    <h1>Calendario de Reservas</h1>
@stop

@section('css')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
    .fc-event-tooltip {
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        font-size: 0.9em;
        white-space: normal;
        max-width: 300px;
    }
    </style>
@stop

@section('content')
<div class="container-fluid mt-4">
        <div class="card"> 
            <div class="card-body">

        <div id="calendar"></div>

        </div>
    </div>
</div>


<!-- Modal del Formulario -->
<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReservaLabel">Nueva Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>


            <div class="modal-body">
    <form id="formReserva" action="{{ route('reservas.store') }}" method="POST" novalidate>
        @csrf <div id="form-nueva-reserva">
            <h5>Nueva Reserva</h5>

            
            <div class="mb-3">
                <label for="espacio_id" class="form-label">Espacio a Reservar</label>
                <div class="d-flex align-items-center">

                    <select class="form-select w-50 me-2" id="espacio_id" name="espacio_id">
                        <option value="" selected disabled>Selecciona un espacio</option>
                        @foreach ($espacios as $espacio)
                            <option value="{{ $espacio->id }}">{{ $espacio->nombre }}</option>
                        @endforeach
                        <option value="Otro">Otro</option>
                    </select>
                    <input type="text" class="form-control w-50 d-none" id="otro_espacio"
                        name="otro_espacio" placeholder="Especifique el espacio" disabled>
                </div>
            </div>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
            <div class="mb-3">
                <label for="hora_inicio" class="form-label">Hora de Inicio</label>
            <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required >
            </div>
            <div class="mb-3">
                <label for="hora_fin" class="form-label">Hora de Fin</label>
                <input type="time" class="form-control" id="hora_fin" name="hora_fin" required >
            </div>

            <div class="mb-3">
                <label for="nombre_actividad" class="form-label">Nombre de la Actividad</label>
                <input type="text" class="form-control" id="nombre_actividad" name="nombre_actividad" required>
            </div>
            <div class="mb-3">
                <label for="programa_evento" class="form-label">Programa del Evento</label>
                <textarea class="form-control" id="programa_evento" name="programa_evento" rows="4"></textarea>
            </div>

            <div class="text-end">
                <button type="button" class="btn btn-secondary" id="btn-siguiente">Siguiente</button>
            </div>
        </div>

        <div id="form-requerimientos" class="d-none">
    <h5 class="mt-4">Requerimientos</h5>

    <div class="mb-3">
        <label for="num_personas" class="form-label">Número de Personas</label>
        <select class="form-control" name="num_personas" required>
            @for ($i = 1; $i <= 500; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </select>
    </div>

    <div>
        <h6>Audiovisuales</h6>
        <div class="row">
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="audiovisuales[]" value="Computador">
                <label class="form-check-label">Computador</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_audiovisuales[Computador]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="audiovisuales[]" value="Cámara">
                <label class="form-check-label">Cámara</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_audiovisuales[Cámara]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="audiovisuales[]" value="Conexión a Internet">
                <label class="form-check-label">Conexión a Internet</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_audiovisuales[Conexión a Internet]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="audiovisuales[]" value="Pantalla para Proyección">
                <label class="form-check-label">Pantalla para Proyección</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_audiovisuales[Pantalla para Proyección]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="audiovisuales[]" value="Pantalla (TV)">
                <label class="form-check-label">Pantalla (TV)</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_audiovisuales[Pantalla (TV)]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="audiovisuales[]" value="Video Bin">
                <label class="form-check-label">Video Bin</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_audiovisuales[Video Bin]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="audiovisuales[]" value="Sonido">
                <label class="form-check-label">Sonido</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_audiovisuales[Sonido]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="audiovisuales[]" value="Micrófono">
                <label class="form-check-label">Micrófono</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_audiovisuales[Micrófono]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="audiovisuales[]" value="Otro" data-target="otro-audiovisual-select">
                <label class="form-check-label">Otro</label>
                <div id="otro-audiovisual-select" class="d-none ms-2 d-inline-block">
                    <input type="text" class="form-control form-control-sm" name="otro_audiovisual" placeholder="Especifica otro">
                    <input type="number" class="form-control form-control-sm" name="cantidad_audiovisuales[Otro]" value="1" min="1" style="width: 80px;">
                </div>
            </div>
        </div>
    </div>

    <h6 class="mt-3">Servicios Generales</h6>
    <div class="row">
        @php
            $serviciosGenerales = ['Mesa', 'Mantel', 'Extensión eléctrica', 'Multitoma'];
            $otroItemServiciosGenerales = 'Otro';
        @endphp
        @foreach ($serviciosGenerales as $item)
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox"
                    name="servicios_generales[]" value="{{ $item }}"
                    data-target="{{ \Str::slug($item) }}-select">
                <label class="form-check-label">{{ $item }}</label>
                <div id="{{ \Str::slug($item) }}-select" class="d-none ms-2 d-inline-block">
                <select class="form-control" name="cantidad_servicios_generales[{{ $item }}]">
                        @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        @endforeach
        <!-- Checkbox para "Otro" en Servicios Generales -->
        <div class="form-check">
            <input class="form-check-input requerimiento-checkbox" type="checkbox"
                name="servicios_generales[]" value="{{ $otroItemServiciosGenerales }}"
                data-target="otro-servicio_general-select">
            <label class="form-check-label">{{ $otroItemServiciosGenerales }}</label>
            <div id="otro-servicio_general-select" class="d-none ms-2 d-inline-block">
                <input type="text" class="form-control" name="otro_servicio_general" placeholder="Especifica otro servicio">
            </div>
        </div>
    </div>

    <div class="mt-3">
        <h6>Comunicaciones</h6>
        <div class="row">
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="comunicaciones[]" value="Fotografía">
                <label class="form-check-label">Fotografía</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_comunicaciones[Fotografía]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="comunicaciones[]" value="Video">
                <label class="form-check-label">Video</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_comunicaciones[Video]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="comunicaciones[]" value="Otro" data-target="otro-comunicacion-select">
                <label class="form-check-label">Otro</label>
                <div id="otro-comunicacion-select" class="d-none ms-2 d-inline-block">
                    <input type="text" class="form-control form-control-sm" name="otro_comunicacion" placeholder="Especifica otra comunicación">
                    <input type="number" class="form-control form-control-sm" name="cantidad_comunicaciones[Otro]" value="1" min="1" style="width: 80px;">
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <h6>Administración</h6>
        <div class="row">
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="administracion[]" value="Refrigerio">
                <label class="form-check-label">Refrigerio</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_administracion[Refrigerio]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="administracion[]" value="Agua">
                <label class="form-check-label">Agua</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_administracion[Agua]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="administracion[]" value="Vasos">
                <label class="form-check-label">Vasos</label>
                <input type="number" class="form-control form-control-sm ms-2 d-inline-block" name="cantidad_administracion[Vasos]" value="1" min="1" style="width: 80px;">
            </div>
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox" name="administracion[]" value="Otro" data-target="otro-administracion-select">
                <label class="form-check-label">Otro</label>
                <div id="otro-administracion-select" class="d-none ms-2 d-inline-block">
                    <input type="text" class="form-control form-control-sm" name="otro_administracion" placeholder="Especifica otro">
                    <input type="number" class="form-control form-control-sm" name="cantidad_administracion[Otro]" value="1" min="1" style="width: 80px;">
                </div>
            </div>
        </div>
    </div>





    <div class="text-end mt-4">
        <button type="button" class="btn btn-secondary" id="btn-anterior">Anterior</button>
        <button type="submit" class="btn btn-success">Reservar</button>
    </div>
</div>
</div>
</div>
    </form>
</div>
    

<div class="modal fade" id="infoReservaModal" tabindex="-1" aria-labelledby="infoReservaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> {{-- Puedes usar modal-lg para más espacio --}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoReservaModalLabel">Detalles de la Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Aquí es donde el JavaScript insertará la información --}}
                <div id="reservaInfoContent">
                    <p>Cargando detalles...</p>
                </div>
            </div>
            <div class="modal-footer">
                {{-- Podrías añadir botones aquí si fueran necesarios, como "Editar Reserva" o "Eliminar" --}}
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

        </div>


</div>
@stop
   



@section('js')
    
    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://unpkg.com/tippy.js@6"></script> {{-- Mantenlo si usas Tippy.js para algo más, si no, puedes quitarlo si solo era para el tooltip anterior --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                height: 'auto',
                contentHeight: 'auto',
                expandRows: true,
                aspectRatio: 1.8,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: '{{ route("reservas.events") }}', 
            
                dateClick: function(info) {
                    console.log('1. Evento dateClick SE HA DISPARADO CORRECTAMENTE.');
                    console.log('2. Fecha seleccionada por el usuario:', info.dateStr);

                    try {
                        const clickedDate = info.dateStr;
                        var fechaInput = document.getElementById('fecha');

                        if (fechaInput) {
                            fechaInput.value = clickedDate;
                            console.log('3. Fecha asignada al input del modal (ID "fecha").');
                        } else {
                            console.error('Error: Input con ID "fecha" no encontrado en el modal.');
                            alert('Error: No se encontró el campo de fecha en el formulario del modal.');
                            return; 
                        }

                        var modalReservaElement = document.getElementById('modalReserva'); 

                        if (modalReservaElement) {
                            console.log('4. Elemento del modal (ID "modalReserva") encontrado en el HTML.');
                            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
                                var modalReserva = new bootstrap.Modal(modalReservaElement);
                                modalReserva.show();
                                console.log('5. Intento de mostrar el modal (modalReserva.show()) finalizado.');
                            } else {
                                console.error('Error: El objeto bootstrap o bootstrap.Modal no está definido.');
                                alert('Error: La librería de Bootstrap para modales no está disponible.');
                                return;
                            }
                        } else {
                            console.error('Error: Elemento del modal con ID "modalReserva" no encontrado en el HTML.');
                            alert('Error: No se encontró el elemento HTML del formulario modal. Revisa los IDs.');
                            return; 
                        }

                        // Limpieza de campos del formulario (estas son las líneas que habías descomentado)
                        document.getElementById('hora_inicio').value = '';
                        document.getElementById('hora_fin').value = '';
                        document.getElementById('nombre_actividad').value = '';
                        document.getElementById('programa_evento').value = '';
                        $('#espacio_id').val('').trigger('change'); // Resetea el select de espacio
                        $('#otro_espacio').addClass('d-none').prop('disabled', true).removeAttr('required').val('');
                        
                        $('.requerimiento-checkbox').prop('checked', false); // Desmarcar todos los checkboxes de requerimientos
                        // Ocultar inputs de cantidad y divs de "otro" específico que podrían estar visibles
                        $('.form-check input[type="number"]').addClass('d-none'); 
                        $('[id^="otro-"][id$="-select"]').addClass('d-none').find('input[type="text"]').val(''); // Oculta y limpia el texto de "otro"

                        $('#form-nueva-reserva').removeClass('d-none'); // Asegura que la primera parte del form sea visible
                        $('#form-requerimientos').addClass('d-none');  // Asegura que la segunda parte del form esté oculta
                        
                    } catch (e) {
                        console.error('Error general dentro de la función dateClick:', e);
                        alert('Ocurrió un error al procesar el clic en la fecha: ' + e.message);
                    }
                },

              // En @section('js'), dentro de la inicialización de FullCalendar:
// ... (tus otras opciones como locale, initialView, events, dateClick, eventMouseEnter, eventMouseLeave) ...

eventClick: function(info) {
    console.log('eventClick disparado para el evento ID:', info.event.id);
    var reservaId = info.event.id;
    var spinner = '<p>Cargando detalles...</p>'; // Mensaje de carga
    var reservaInfoContentDiv = document.getElementById('reservaInfoContent');

    if (!reservaInfoContentDiv) {
        console.error('Error: Elemento con ID "reservaInfoContent" no encontrado en el HTML del modal de detalles.');
        alert('Error de configuración: falta el contenedor para mostrar detalles de la reserva.');
        return;
    }

    reservaInfoContentDiv.innerHTML = spinner; // Mostrar "Cargando..."

    var infoModalElement = document.getElementById('infoReservaModal');
    if (!infoModalElement) {
        console.error('Error: Elemento del modal con ID "infoReservaModal" no encontrado.');
        alert('Error de configuración: falta el modal para mostrar detalles.');
        return;
    }
    var infoModal = new bootstrap.Modal(infoModalElement);
    infoModal.show(); // Muestra el modal de detalles inmediatamente con el mensaje "Cargando..."

    fetch('/reservas/' + reservaId) // La URL para obtener los detalles de una reserva
        .then(response => {
            if (!response.ok) {
                // Si la respuesta no es OK, intentamos obtener el mensaje de error
                return response.text().then(text => {
                    throw new Error(`Error del servidor: ${response.status} ${response.statusText}. Detalles: ${text}`);
                });
            }
            return response.json(); // Esperamos una respuesta JSON
        })
        .then(data => {
            console.log('Detalles de la reserva recibidos:', data);
            
            // Formatear las horas
            const formattedStartTime = data.hora_inicio ? data.hora_inicio.substring(0, 5) : 'No definida';
            const formattedEndTime = data.hora_fin ? data.hora_fin.substring(0, 5) : 'No definida';

            let contenidoHtml = `
                <p><strong>Actividad:</strong> ${data.nombre_actividad || 'No disponible'}</p>
                <p><strong>Fecha:</strong> ${data.fecha || 'No disponible'}</p>
                <p><strong>Hora Inicio:</strong> ${formattedStartTime}</p>
                <p><strong>Hora Fin:</strong> ${formattedEndTime}</p>
                <p><strong>Espacio:</strong> ${data.espacio ? data.espacio.nombre : (data.otro_espacio || 'No especificado')}</p>
                <p><strong>Número de Personas:</strong> ${data.num_personas !== null && data.num_personas !== undefined ? data.num_personas : 'No especificado'}</p>
                <p><strong>Programa del Evento:</strong></p>
                <p>${data.programa_evento || 'No especificado'}</p>
            `;

            if (data.requerimientos && data.requerimientos.length > 0) {
                contenidoHtml += '<p><strong>Requerimientos:</strong></p><ul>';
                data.requerimientos.forEach(req => {
                    contenidoHtml += `<li>${req.descripcion ? req.descripcion : ''} ${req.cantidad ? '(Cantidad: ' + req.cantidad + ')' : ''}</li>`;
                });
                contenidoHtml += '</ul>';
            } else {
                contenidoHtml += '<p><strong>Requerimientos:</strong> No se solicitaron requerimientos.</p>';
            }

            if (data.usuario) { // Si la información del usuario está cargada
                contenidoHtml += `<p><strong>Reservado por:</strong> ${data.usuario.name || 'No disponible'}</p>`;
            }

            reservaInfoContentDiv.innerHTML = contenidoHtml; // Inserta el contenido formateado
        })
        .catch(error => {
            console.error('Error al cargar los detalles de la reserva (eventClick):', error);
            reservaInfoContentDiv.innerHTML = `<p class="text-danger">Error al cargar los detalles: ${error.message}</p>`;
            // No cerramos el modal aquí, para que el usuario vea el mensaje de error.
        });
},


                eventMouseEnter: function(info) {
                    const props = info.event.extendedProps; 

                    const startTime = new Date(info.event.start);
                    const formattedStartTime = startTime.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true });

                    let formattedEndTime = 'No definida';
                    if (info.event.end) { 
                        const endTime = new Date(info.event.end);
                        formattedEndTime = endTime.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true });
                    }

                    let contenidoTooltip = `
                        <div class="fc-event-tooltip-inner">
                            <b>Usuario:</b> ${props.usuario_nombre ? props.usuario_nombre : 'No disponible'}<br>
                            <b>Actividad:</b> ${info.event.title}<br>
                            <b>Hora Inicio:</b> ${formattedStartTime}<br>
                            <b>Hora Fin:</b> ${formattedEndTime}<br>
                            <b>Espacio:</b> ${props.espacio_nombre ? props.espacio_nombre : 'No disponible'}<br>
                            <b>Número de Personas:</b> ${props.num_personas !== null && props.num_personas !== undefined ? props.num_personas : 'No especificado'}<br>
                            <b>Requerimientos:</b><br>
                    `;

                    if (props.requerimientosArray && props.requerimientosArray.length > 0) {
                        props.requerimientosArray.forEach(req => {
                            contenidoTooltip += `- ${req.descripcion ? req.descripcion : ''} ${req.cantidad ? '(Cantidad: ' + req.cantidad + ')' : ''}<br>`;
                        });
                    } else {
                        contenidoTooltip += 'No se solicitaron requerimientos.<br>';
                    }
                    contenidoTooltip += `</div>`;

                    let tooltipEl = document.getElementById('fc-custom-tooltip'); 
                    if (!tooltipEl) { 
                        tooltipEl = document.createElement('div');
                        tooltipEl.id = 'fc-custom-tooltip';
                        tooltipEl.style.position = 'absolute';
                        tooltipEl.style.zIndex = '10000'; 
                        tooltipEl.style.backgroundColor = '#fff';
                        tooltipEl.style.border = '1px solid #ccc';
                        tooltipEl.style.padding = '8px 12px';
                        tooltipEl.style.borderRadius = '4px';
                        tooltipEl.style.boxShadow = '0 2px 5px rgba(0,0,0,0.15)';
                        tooltipEl.style.fontSize = '0.875em';
                        tooltipEl.style.whiteSpace = 'normal';
                        tooltipEl.style.maxWidth = '300px';
                        document.body.appendChild(tooltipEl);
                    }
                    
                    tooltipEl.innerHTML = contenidoTooltip;
                    tooltipEl.style.display = 'block';

                    tooltipEl.style.left = info.jsEvent.pageX + 15 + 'px'; 
                    tooltipEl.style.top = info.jsEvent.pageY + 15 + 'px';
                },

                eventMouseLeave: function(info) {
                    let tooltipEl = document.getElementById('fc-custom-tooltip');
                    if (tooltipEl) {
                        tooltipEl.style.display = 'none';
                        tooltipEl.innerHTML = ''; 
                    }
                }
                 }); 

            calendar.render();

            // Forzar actualización del tamaño por si acaso
            setTimeout(function() {
                if (calendar) { // Verificar que calendar exista
                    calendar.updateSize();
                }
            }, 250);

            // Lógica jQuery para el formulario modal (interacciones de campos)
            $(document).ready(function() {
                // Mostrar/ocultar campo "Otro espacio"
                $('#espacio_id').on('change', function() {
                    if ($(this).val() === 'Otro') {
                        $('#otro_espacio').removeClass('d-none').prop('disabled', false).prop('required', true); // Hacemos 'otro_espacio' required si "Otro" es seleccionado
                    } else {
                        $('#otro_espacio').addClass('d-none').prop('disabled', true).prop('required', false).val('');
                    }
                });

                // Navegación entre secciones del formulario
                $('#btn-siguiente').click(() => {
                    // Aquí podrías añadir validación de la primera parte del formulario antes de pasar
                    $('#form-nueva-reserva').addClass('d-none');
                    $('#form-requerimientos').removeClass('d-none');
                });

                $('#btn-anterior').click(() => {
                    $('#form-requerimientos').addClass('d-none');
                    $('#form-nueva-reserva').removeClass('d-none');
                });

                // Mostrar/ocultar inputs de cantidad y divs de "otro" para requerimientos
                // (Esta lógica ya la tenías y parece correcta para la UI)
                $(document).on('change', '.requerimiento-checkbox', function() {
                    const isChecked = this.checked;
                    const targetId = $(this).data('target'); 
                    const cantidadInput = $(this).siblings('input[type="number"]').first();

                    if (cantidadInput.length > 0) {
                        cantidadInput.toggleClass('d-none', !isChecked);
                        if(!isChecked) cantidadInput.val('1'); 
                    }

                    if (targetId) { 
                        const targetDiv = $('#' + targetId); // ej. #otro-audiovisual-select
                        targetDiv.toggleClass('d-none', !isChecked);
                        if(!isChecked) targetDiv.find('input[type="text"], input[type="number"]').val(''); 
                    }
                });

                // Inicialmente ocultar los campos de cantidad y divs "otro" que no estén marcados
                $('.requerimiento-checkbox').each(function() {
                    if (!$(this).prop('checked')) {
                        $(this).siblings('input[type="number"]').first().addClass('d-none');
                        const targetId = $(this).data('target');
                        if (targetId) {
                            $('#' + targetId).addClass('d-none');
                        }
                    }
                });
            }); // Fin de $(document).ready

            // Lógica para el envío del formulario con AJAX
            document.getElementById('formReserva').addEventListener('submit', function(e) {
                e.preventDefault();
                Log.info('Formulario de reserva enviado vía AJAX'); // Para depurar si se dispara el submit

                fetch(this.action, {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // Forma robusta de obtener CSRF token
                        'Accept': 'application/json', // Indicar que esperamos JSON
                    },
                    body: new FormData(this)
                })
                .then(response => {
                    if (!response.ok) {
                        // Si la respuesta no es OK, intenta parsear como JSON si es posible, o usa el statusText
                        return response.json().catch(() => {
                            throw new Error(`Error del servidor: ${response.status} ${response.statusText}`);
                        }).then(errorData => {
                            // Si el cuerpo del error es JSON y tiene un mensaje, úsalo
                            throw new Error(errorData.message || `Error del servidor: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    Log.info('Reserva creada exitosamente vía AJAX:', data);
                    calendar.refetchEvents();
                    this.reset(); 
                    $('#modalReserva').modal('hide'); // Usando jQuery para ocultar el modal
                    
                    // Resetear visualmente el formulario a su estado inicial
                    $('#espacio_id').val('').trigger('change');
                    $('#otro_espacio').addClass('d-none').prop('disabled', true).prop('required', false).val('');
                    $('.requerimiento-checkbox').prop('checked', false);
                    $('.form-check input[type="number"]').addClass('d-none').val('1');
                    $('[id^="otro-"][id$="-select"]').addClass('d-none').find('input').val('');
                    $('#form-nueva-reserva').removeClass('d-none');
                    $('#form-requerimientos').addClass('d-none');

                    alert(data.message || '¡Reserva creada exitosamente!');
                })
                .catch(error => {
                    console.error('Error al crear la reserva (AJAX catch):', error);
                    alert('Hubo un error al crear la reserva: ' + error.message + '. Por favor, revisa la consola.');
                });
            }); // Fin de addEventListener('submit')

        }); // Fin de document.addEventListener('DOMContentLoaded')
    </script>
@stop