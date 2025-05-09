@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="text-center">Calendario de Reservas</h1>
    <div id="calendar"></div>
</div>


<!-- Modal del Formulario (copiado desde index.blade.php con ajustes) -->
<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReservaLabel">Nueva Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>


            <div class="modal-body">
    <form id="formReserva" action="{{ route('reservas.store') }}" method="POST">
        @csrf <div id="form-nueva-reserva">
            <h5>Nueva Reserva</h5>

            
            <div class="mb-3">
                <label for="espacio_id" class="form-label">Espacio a Reservar</label>
                <div class="d-flex align-items-center">

                    <select class="form-select w-50 me-2" id="espacio_id" name="espacio_id" required>
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
        {{-- Checkbox para "Otro" en Servicios Generales --}}
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
      
        </div>

<!-- modal para editar y eliminar reserva -->
<div class="modal fade" id="infoReservaModal" tabindex="-1" aria-labelledby="infoReservaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoReservaModalLabel">Detalles de la Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="reservaInfoContent">
                    </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary me-2" id="btnEditarReserva">Editar</button>
                    <button type="button" class="btn btn-danger" id="btnEliminarReserva">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<!-- Dependencias -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>    

<!-- Scripts Personalizados -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar'); // Asegúrate de que el ID sea 'calendar'
    var calendar = new FullCalendar.Calendar(calendarEl, {
        // ... otras opciones de tu calendario ...
        events: '/reservas/events',
        dateClick: function(info) {
            // Formatear la fecha para el input del formulario (YYYY-MM-DD)
            const clickedDate = info.dateStr;
            document.getElementById('fecha').value = clickedDate;

            // Limpiar los campos de hora si es necesario
            document.getElementById('hora_inicio').value = '';
            document.getElementById('hora_fin').value = '';
            document.getElementById('nombre_actividad').value = '';
            document.getElementById('programa_evento').value = '';
            $('#espacio_id').val('').trigger('change'); // Resetear el select de espacio
            $('#otro_espacio').addClass('d-none').prop('disabled', true).removeAttr('required').val('');

            // Desmarcar y ocultar campos de requerimientos
            $('.requerimiento-checkbox').prop('checked', false);
            $('.form-check input[type="number"]').addClass('d-none');

            // Mostrar la primera sección del formulario
            $('#form-nueva-reserva').removeClass('d-none');
            $('#form-requerimientos').addClass('d-none');

            // Mostrar el modal de creación (asegúrate de que el ID de tu modal de creación sea 'modalReserva')
            var modalReserva = new bootstrap.Modal(document.getElementById('modalReserva'));
            modalReserva.show();
        },

        eventClick: function(info) {
            var reservaId = info.event.id;

            fetch('/reservas/' + reservaId) // Nueva ruta para obtener solo los detalles para mostrar
                .then(response => response.json())
                .then(data => {
                    // Formatear las horas para mostrar
                    const formattedStartTime = data.hora_inicio.substring(0, 5); // Obtener solo HH:MM
                    const formattedEndTime = data.hora_fin.substring(0, 5); // Obtener solo HH:MM

                    // Crear el contenido HTML para mostrar en el modal
                    let content = `
                        <p><strong>Actividad:</strong> ${data.nombre_actividad}</p>
                        <p><strong>Fecha:</strong> ${data.fecha}</p>
                        <p><strong>Hora Inicio:</strong> ${formattedStartTime}</p>
                        <p><strong>Hora Fin:</strong> ${formattedEndTime}</p>
                        <p><strong>Espacio:</strong> ${data.espacio ? data.espacio.nombre : (data.espacio_nombre ? data.espacio_nombre : 'No disponible')}</p>
                        <p><strong>Número de Personas:</strong> ${data.num_personas}</p>
                    `;

                    // Mostrar los requerimientos
                    if (data.requerimientos && data.requerimientos.length > 0) {
                        content += '<p><strong>Requerimientos:</strong></p><ul>';
                        data.requerimientos.forEach(req => {
                            content += `<li>${req.descripcion} ${req.cantidad ? '(Cantidad: ' + req.cantidad + ')' : ''}</li>`;
                        });
                        content += '</ul>';
                    } else {
                        content += '<p><strong>Requerimientos:</strong> No se solicitaron requerimientos.</p>';
                    }

                    // Insertar el contenido en el modal
                    document.getElementById('reservaInfoContent').innerHTML = content;

                    // Configurar el botón "Editar" para redirigir o abrir el modal de edición
                    document.getElementById('btnEditarReserva').onclick = function() {
                        // ... (código para abrir el modal de edición - sin cambios por ahora) ...
                    };

                    // Configurar el botón "Eliminar"
                    document.getElementById('btnEliminarReserva').onclick = function() {
                        // ... (código para eliminar la reserva - sin cambios por ahora) ...
                    };

                    // Mostrar el modal de información
                    var infoModal = new bootstrap.Modal(document.getElementById('infoReservaModal'));
                    infoModal.show();
                })
                .catch(error => {
                    console.error('Error al cargar los detalles de la reserva:', error);
                    alert('Error al cargar los detalles de la reserva.');
                });
        },
        eventMouseEnter: function(info) {
            const reserva = info.event.extendedProps;

            // Formatear hora de inicio
            const startTime = new Date(info.event.start);
            const formattedStartTime = startTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // Formatear hora de fin
            const endTime = new Date(info.event.end);
            const formattedEndTime = endTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            let contenidoTooltip = `
                <div class="fc-event-tooltip">
                    <b>Usuario:</b> ${reserva.usuario ? reserva.usuario : 'No disponible'}<br>
                    <b>Actividad:</b> ${info.event.title}<br>
                    <b>Hora Inicio:</b> ${formattedStartTime}<br>
                    <b>Hora Fin:</b> ${formattedEndTime}<br>
                    <b>Espacio:</b> ${reserva.espacio ? reserva.espacio : (reserva.espacio_nombre ? reserva.espacio_nombre : 'No disponible')}<br>
                    <b>Número de Personas:</b> ${reserva.num_personas}<br>
                    <b>Requerimientos:</b><br>
            `;

            // Mostrar requerimientos con cantidad
            if (reserva.requerimientosArray && reserva.requerimientosArray.length > 0) {
                reserva.requerimientosArray.forEach(req => {
                    contenidoTooltip += `- ${req.descripcion} ${req.cantidad ? '(Cantidad: ' + req.cantidad + ')' : ''}<br>`;
                });
            } else {
                contenidoTooltip += 'No se solicitaron requerimientos.<br>';
            }
            contenidoTooltip += `</div>`;

            // Crear el tooltip element
            let tooltipEl = document.createElement('div');
            tooltipEl.innerHTML = contenidoTooltip;
            document.body.appendChild(tooltipEl);

            // Posicionar el tooltip cerca del cursor
            const x = info.jsEvent.pageX;
            const y = info.jsEvent.pageY;

            tooltipEl.style.position = 'absolute';
            tooltipEl.style.left = x + 10 + 'px'; // Ajusta la distancia del cursor si es necesario
            tooltipEl.style.top = y + 10 + 'px';  // Ajusta la distancia del cursor si es necesario
            tooltipEl.style.zIndex = '1000'; // Asegura que esté por encima de otros elementos

            // Guarda una referencia al tooltip para poder eliminarlo en mouseleave
            info.el.setAttribute('data-tooltip-id', tooltipEl);
        },
        eventMouseLeave: function(info) {
            const tooltip = document.querySelector('.fc-event-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
            // También podemos intentar eliminar cualquier tooltip residual por si acaso
            const allTooltips = document.querySelectorAll('.fc-event-tooltip');
            allTooltips.forEach(tt => tt.remove());
        }
    });
    calendar.render();

    // Lógica jQuery del Formulario (se mantiene igual)
    $(document).ready(function() {
        // Mostrar campo "Otro espacio" en el formulario de creación
        $('#espacio_id').on('change', function() {
            if ($(this).val() === 'Otro') {
                $('#otro_espacio').removeClass('d-none').prop('disabled', false).attr('required', true);
            } else {
                $('#otro_espacio').addClass('d-none').prop('disabled', true).removeAttr('required').val('');
            }
        });

        // Mostrar campo "Otro espacio" en el formulario de edición
        $('#edit_espacio_id').on('change', function() {
            if ($(this).val() === 'Otro') {
                $('#edit_otro_espacio_container').show();
                $('#edit_otro_espacio').attr('required', true);
            } else {
                $('#edit_otro_espacio_container').hide();
                $('#edit_otro_espacio').removeAttr('required').val('');
                $('#edit_otro_espacio').val('');
            }
        });

        // Navegación entre secciones del formulario de creación
        $('#btn-siguiente').click(() => {
            $('#form-nueva-reserva').addClass('d-none');
            $('#form-requerimientos').removeClass('d-none');
        });

        $('#btn-anterior').click(() => {
            $('#form-requerimientos').addClass('d-none');
            $('#form-nueva-reserva').removeClass('d-none');
        });

        // Mostrar/ocultar elementos al marcar/desmarcar checkboxes en el formulario de creación
        $(document).on('change', '.requerimiento-checkbox', function() {
            const target = $(this).data('target');
            if (target) {
                $(`#${target}`).toggleClass('d-none', !this.checked);
            }
            // Mostrar/ocultar el input de cantidad asociado
            const cantidadInput = $(this).nextAll('input[type="number"]').first();
            if (cantidadInput.length > 0) {
                cantidadInput.toggleClass('d-none', !this.checked);
            }
        });

        // Mostrar/ocultar elementos al marcar/desmarcar checkboxes en el formulario de edición
        $(document).on('change', '#editarReservaModal .requerimiento-checkbox', function() {
            const target = $(this).data('target');
            if (target) {
                $(`#${target}`).toggleClass('d-none', !this.checked);
            }
            // Mostrar/ocultar el input de cantidad asociado
            const cantidadInput = $(this).nextAll('input[type="number"]').first();
            if (cantidadInput.length > 0) {
                cantidadInput.toggleClass('d-none', !this.checked);
            }
        });

        // Inicialmente ocultar los campos de cantidad que no estén marcados en el formulario de creación
        $('.form-check').each(function() {
            const checkbox = $(this).find('.requerimiento-checkbox');
            const cantidadInput = $(this).find('input[type="number"]');
            if (!checkbox.prop('checked')) {
                cantidadInput.addClass('d-none');
            }
        });

        // Inicialmente ocultar los campos de cantidad que no estén marcados en el formulario de edición
        $('#editarReservaModal .form-check').each(function() {
            const checkbox = $(this).find('.requerimiento-checkbox');
            const cantidadInput = $(this).find('input[type="number"]');
            if (!checkbox.prop('checked')) {
                cantidadInput.addClass('d-none');
            }
        });
    });

    // Envío del Formulario de Creación con AJAX (se mantiene igual)
    document.getElementById('formReserva').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(this.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new FormData(this)
        }).then(response => {
            if (response.ok) {
                calendar.refetchEvents();
                this.reset();
                bootstrap.Modal.getInstance(document.getElementById('modalReserva')).hide();
                $('#form-nueva-reserva').removeClass('d-none');
                $('#form-requerimientos').addClass('d-none');
                alert('¡Reserva creada exitosamente!'); // Agregamos esta línea
            } else {
                // Aquí podríamos agregar lógica para manejar errores si la reserva falla
                console.error('Error al crear la reserva:', response);
                alert('Hubo un error al crear la reserva. Por favor, inténtalo de nuevo.');
            }
        });
    });
});
</script>

<style>
.fc-event-tooltip {
    background-color: #f9f9f9;
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
    font-size: 0.9em;
    white-space: normal; /* Permite que el texto se ajuste */
    max-width: 300px; /* O el ancho que prefieras */
}
</style>

@endsection