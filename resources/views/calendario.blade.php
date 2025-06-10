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
    <div class="d-flex flex-column gap-2">
        @php
            $audiovisualesItems = ['Computador', 'Cámara', 'Conexión a Internet', 'Pantalla para Proyección', 'Pantalla (TV)', 'Video Bin', 'Sonido', 'Micrófono'];
        @endphp
        @foreach ($audiovisualesItems as $item)
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox"
                       name="audiovisuales[]" value="{{ $item }}">
                <label class="form-check-label">{{ $item }}</label>
                {{-- Contenedor para el input numérico, con d-none inicial --}}
                <div class="ms-2 d-inline-block d-none">
                    <input type="number" class="form-control form-control-sm"
                           name="cantidad_audiovisuales[{{ $item }}]" value="1" min="1" style="width: 80px;">
                </div>
            </div>
        @endforeach
        {{-- "Otro" para Audiovisuales (ya estaba bien estructurado) --}}
        <div class="form-check">
            <input class="form-check-input requerimiento-checkbox" type="checkbox"
                   name="audiovisuales[]" value="Otro" data-target="otro-audiovisual-select">
            <label class="form-check-label">Otro</label>
            <div id="otro-audiovisual-select" class="d-none ms-2 d-inline-block">
                <input type="text" class="form-control form-control-sm" name="otro_audiovisual" placeholder="Especifica otro">
                <input type="number" class="form-control form-control-sm" name="cantidad_audiovisuales[Otro]" value="1" min="1" style="width: 80px;">
            </div>
        </div>
    </div>
</div>



    <h6 class="mt-3">Servicios Generales</h6>
    <div class="d-flex flex-column gap-2">
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
    <div class="d-flex flex-column gap-2">
        @php
            $comunicacionesItems = ['Fotografía', 'Video'];
        @endphp
        @foreach ($comunicacionesItems as $item)
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox"
                       name="comunicaciones[]" value="{{ $item }}">
                <label class="form-check-label">{{ $item }}</label>
                {{-- Contenedor para el input numérico, con d-none inicial --}}
                <div class="ms-2 d-inline-block d-none">
                    <input type="number" class="form-control form-control-sm"
                           name="cantidad_comunicaciones[{{ $item }}]" value="1" min="1" style="width: 80px;">
                </div>
            </div>
        @endforeach
        {{-- "Otro" para Comunicaciones (ya estaba bien estructurado) --}}
        <div class="form-check">
            <input class="form-check-input requerimiento-checkbox" type="checkbox"
                   name="comunicaciones[]" value="Otro" data-target="otro-comunicacion-select">
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
    <div class="d-flex flex-column gap-2">
        @php
            $administracionItems = ['Refrigerio', 'Agua', 'Vasos'];
        @endphp
        @foreach ($administracionItems as $item)
            <div class="form-check">
                <input class="form-check-input requerimiento-checkbox" type="checkbox"
                       name="administracion[]" value="{{ $item }}">
                <label class="form-check-label">{{ $item }}</label>
                {{-- Contenedor para el input numérico, con d-none inicial --}}
                <div class="ms-2 d-inline-block d-none">
                    <input type="number" class="form-control form-control-sm"
                           name="cantidad_administracion[{{ $item }}]" value="1" min="1" style="width: 80px;">
                </div>
            </div>
        @endforeach
        {{-- "Otro" para Administración (ya estaba bien estructurado) --}}
        <div class="form-check">
            <input class="form-check-input requerimiento-checkbox" type="checkbox"
                   name="administracion[]" value="Otro" data-target="otro-administracion-select">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
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

        // Cuando se da clic sobre una fecha
        dateClick: function(info) {
            const clickedDate = info.dateStr;
            const fechaInput = document.getElementById('fecha');
            const modalReservaElement = document.getElementById('modalReserva');

            if (!fechaInput) return alert('Error: No se encontró el campo de fecha.');
            fechaInput.value = clickedDate;

            if (!modalReservaElement || typeof bootstrap?.Modal === 'undefined') {
                return alert('Error: No se pudo mostrar el modal.');
            }

            new bootstrap.Modal(modalReservaElement).show();

            // Resetear valores del formulario
            ['hora_inicio', 'hora_fin', 'nombre_actividad', 'programa_evento'].forEach(id => {
                document.getElementById(id).value = '';
            });

            $('#espacio_id').val('').trigger('change');
            $('#otro_espacio').addClass('d-none').prop('disabled', true).val('');
            $('.requerimiento-checkbox').prop('checked', false);
            $('.form-check input[type="number"]').addClass('d-none');
            $('[id^="otro-"][id$="-select"]').addClass('d-none').find('input[type="text"]').val('');

            $('#form-nueva-reserva').removeClass('d-none');
            $('#form-requerimientos').addClass('d-none');
        },

        // Cuando se da clic sobre un evento
        eventClick: function(info) {
            const reservaId = info.event.id;
            const reservaInfoContentDiv = document.getElementById('reservaInfoContent');
            const infoModalElement = document.getElementById('infoReservaModal');

            if (!reservaInfoContentDiv) return alert('Error: Contenedor de detalles no encontrado.');
            if (!infoModalElement) return alert('Error: Modal de detalles no encontrado.');

            reservaInfoContentDiv.innerHTML = '<p>Cargando detalles...</p>';
            new bootstrap.Modal(infoModalElement).show();

            fetch('/reservas/' + reservaId)
                .then(response => response.ok ? response.json() : response.text().then(text => { throw new Error(text); }))
                .then(data => {
                    const horaIni = data.hora_inicio?.substring(0, 5) || 'No definida';
                    const horaFin = data.hora_fin?.substring(0, 5) || 'No definida';

                    let html = `
                        <p><strong>Actividad:</strong> ${data.nombre_actividad || 'No disponible'}</p>
                        <p><strong>Fecha:</strong> ${data.fecha || 'No disponible'}</p>
                        <p><strong>Hora Inicio:</strong> ${horaIni}</p>
                        <p><strong>Hora Fin:</strong> ${horaFin}</p>
                        <p><strong>Espacio:</strong> ${data.espacio?.nombre || data.otro_espacio || 'No especificado'}</p>
                        <p><strong>Número de Personas:</strong> ${data.num_personas ?? 'No especificado'}</p>
                        <p><strong>Programa del Evento:</strong><br>${data.programa_evento || 'No especificado'}</p>`;

                    if (data.requerimientos?.length > 0) {
                        html += '<p><strong>Requerimientos:</strong></p><ul>';
                        data.requerimientos.forEach(req => {
                            html += `<li>${req.descripcion || ''} ${req.cantidad ? '(Cantidad: ' + req.cantidad + ')' : ''}</li>`;
                        });
                        html += '</ul>';
                    } else {
                        html += '<p><strong>Requerimientos:</strong> No se solicitaron.</p>';
                    }

                    if (data.usuario) {
                        html += `<p><strong>Reservado por:</strong> ${data.usuario.name || 'No disponible'}</p>`;
                    }

                    reservaInfoContentDiv.innerHTML = html;
                })
                .catch(error => {
                    reservaInfoContentDiv.innerHTML = `<p class="text-danger">Error al cargar: ${error.message}</p>`;
                });
        },

        // Tooltip personalizado
        eventMouseEnter: function(info) {
            const props = info.event.extendedProps;
            const startTime = new Date(info.event.start).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true });
            const endTime = info.event.end ? new Date(info.event.end).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true }) : 'No definida';

            let contenido = `
                <div class="fc-event-tooltip-inner">
                    <b>Usuario:</b> ${props.usuario_nombre || 'No disponible'}<br>
                    <b>Actividad:</b> ${info.event.title}<br>
                    <b>Hora Inicio:</b> ${startTime}<br>
                    <b>Hora Fin:</b> ${endTime}<br>
                    <b>Espacio:</b> ${props.espacio_nombre || 'No disponible'}<br>
                    <b>Número de Personas:</b> ${props.num_personas ?? 'No especificado'}<br>
                    <b>Requerimientos:</b><br>`;

            contenido += props.requerimientosArray?.length > 0
                ? props.requerimientosArray.map(r => `- ${r.descripcion || ''} ${r.cantidad ? '(Cantidad: ' + r.cantidad + ')' : ''}<br>`).join('')
                : 'No se solicitaron.<br>';

            contenido += '</div>';

            let tooltipEl = document.getElementById('fc-custom-tooltip');
            if (!tooltipEl) {
                tooltipEl = document.createElement('div');
                tooltipEl.id = 'fc-custom-tooltip';
                Object.assign(tooltipEl.style, {
                    position: 'absolute', zIndex: '10000', backgroundColor: '#fff',
                    border: '1px solid #ccc', padding: '8px 12px', borderRadius: '4px',
                    boxShadow: '0 2px 5px rgba(0,0,0,0.15)', fontSize: '0.875em',
                    whiteSpace: 'normal', maxWidth: '300px'
                });
                document.body.appendChild(tooltipEl);
            }

            tooltipEl.innerHTML = contenido;
            tooltipEl.style.display = 'block';
            tooltipEl.style.left = info.jsEvent.pageX + 15 + 'px';
            tooltipEl.style.top = info.jsEvent.pageY + 15 + 'px';
        },

        eventMouseLeave: function() {
            const tooltipEl = document.getElementById('fc-custom-tooltip');
            if (tooltipEl) {
                tooltipEl.style.display = 'none';
                tooltipEl.innerHTML = '';
            }
        }
    });

    calendar.render();
    setTimeout(() => calendar?.updateSize(), 250);

    // ==== Interacciones de checkbox con inputs relacionados ====
    const checkboxes = document.querySelectorAll('.form-check input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const relatedInputs = checkbox.closest('.form-check')
                .querySelectorAll('input[type="number"], select, input[type="text"], textarea');

            relatedInputs.forEach(input => {
                input.classList.toggle('d-none', !checkbox.checked);
            });
        });
        checkbox.dispatchEvent(new Event('change'));
    });

    // ==== jQuery para cambiar de pasos en el formulario ====
    $(document).ready(function () {
        $('#espacio_id').on('change', function () {
            if ($(this).val() === 'Otro') {
                $('#otro_espacio').removeClass('d-none').prop('disabled', false).prop('required', true);
            } else {
                $('#otro_espacio').addClass('d-none').prop('disabled', true).prop('required', false).val('');
            }
        });

        $('#btn-siguiente').click(() => {
            $('#form-nueva-reserva').addClass('d-none');
            $('#form-requerimientos').removeClass('d-none');
        });

        $('#btn-anterior').click(() => {
            $('#form-requerimientos').addClass('d-none');
            $('#form-nueva-reserva').removeClass('d-none');
        });
    });
});
</script>
@endsection
