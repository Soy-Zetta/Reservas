// 📁 calendario.js
const eventosUrl = document.querySelector('meta[name="url-events"]')?.content || '/reservas/events';

// Mostrar modal de reserva
window.addEventListener('abrir-modal-reserva', function (e) {
    console.log('✅ Se recibió el evento abrir-modal-reserva');

    const modal = document.getElementById('modalReserva');
    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    if (e.detail?.fecha) {
        const input = document.getElementById('campoFecha');
        if (input) input.value = e.detail.fecha;
    }

    mostrarPaso(1); // Reiniciar al paso 1
});


// Funciones para pasos
let pasoActual = 1;
function mostrarPaso(paso) {
    console.log('🔍 Ejecutando mostrarPaso con paso:', paso);
    pasoActual = paso;

    const paso1 = document.getElementById('formPaso1');
    const paso2 = document.getElementById('formPaso2');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnSiguiente = document.getElementById('btnSiguiente');
    const btnEnviar = document.getElementById('btnEnviar');

    if (paso === 1) {
        paso1.classList.remove('hidden');
        paso1.style.display = 'block';
        paso2.classList.add('hidden');
        paso2.style.display = 'none';

        btnAnterior.classList.add('hidden');
        btnAnterior.style.display = 'none';
        btnSiguiente.classList.remove('hidden');
        btnSiguiente.style.display = 'inline-block';
        btnEnviar.classList.add('hidden');
        btnEnviar.style.display = 'none';
    } else {
        paso1.classList.add('hidden');
        paso1.style.display = 'none';
        paso2.classList.remove('hidden');
        paso2.style.display = 'block';

        btnAnterior.classList.remove('hidden');
        btnAnterior.style.display = 'inline-block';
        btnSiguiente.classList.add('hidden');
        btnSiguiente.style.display = 'none';
        btnEnviar.classList.remove('hidden');
        btnEnviar.style.display = 'inline-block';
    }
}


// Botones paso a paso
document.getElementById('btnSiguiente')?.addEventListener('click', () => mostrarPaso(2));
document.getElementById('btnAnterior')?.addEventListener('click', () => mostrarPaso(1));
document.getElementById('btnEnviar')?.addEventListener('click', async (e) => {
    e.preventDefault();

    const formData = new FormData();

    // Paso 1
    formData.append('espacio_id', document.getElementById('campoEspacio')?.value || '');
    formData.append('otro_espacio', document.getElementById('otroEspacio')?.value || '');
    formData.append('fecha', document.getElementById('campoFecha')?.value || '');
    formData.append('hora_inicio', document.getElementById('campoHoraInicio')?.value || '');
    formData.append('hora_fin', document.getElementById('campoHoraFinal')?.value || '');
    formData.append('nombre_actividad', document.getElementById('campoActividad')?.value || '');
    formData.append('programa_evento', document.getElementById('campoPrograma')?.value || '');
    formData.append('num_personas', document.getElementById('campoPersonas')?.value || '');

    console.log('📅 Fecha:', document.getElementById('campoFecha')?.value);
    console.log('⏰ Hora inicio:', document.getElementById('campoHoraInicio')?.value);
    console.log('⏰ Hora fin:', document.getElementById('campoHoraFinal')?.value);


    // Paso 2 - Requerimientos
    const categorias = ['audiovisuales', 'servicios_generales', 'comunicaciones', 'administracion'];

    categorias.forEach(cat => {
        const checkboxes = document.querySelectorAll(`input[name="${cat}[]"]:checked`);
        checkboxes.forEach(checkbox => {
            formData.append(`${cat}[]`, checkbox.value);
            const cantidadInput = checkbox.parentElement.querySelector('input[type="number"], select');
            if (cantidadInput) {
                formData.append(`cantidad_${cat}[${checkbox.value}]`, cantidadInput.value || '');
            }
        });

        const otroText = document.querySelector(`#otro_${cat} input[type="text"]`);
        const otroCantidad = document.querySelector(`#otro_${cat} input[type="number"]`);

        if (otroText && otroCantidad && otroText.value.trim() !== '') {
            formData.append(`otro_${cat}`, otroText.value);
            formData.append(`cantidad_${cat}[Otro]`, otroCantidad.value || '1');
        }
    });

        const reservaId = window.reservaEditandoId;
        const url = reservaId ? `/reservas/${reservaId}` : '/reservas';
        const method = 'POST'; // Siempre POST, Laravel se encargará de simular PUT

        if (reservaId) {
            formData.append('_method', 'PUT'); // Esto le dice a Laravel que es una actualización
        }


    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        if (!response.ok) {
            const error = await response.json();
            console.error('❌ Error al guardar:', error);
            alert('Error al guardar la reserva. Revisa los campos.');
            return;
        }

        alert(reservaId ? '✅ Reserva actualizada correctamente' : '✅ Reserva creada correctamente');

        window.reservaEditandoId = null; // Limpiar estado de edición
        cerrarModal();
        location.reload();
    } catch (err) {
        console.error('❌ Error de red o servidor:', err);
        alert('Hubo un error al guardar. Intenta de nuevo.');
    }
});


// Cerrar modal
function cerrarModal() {
    const modal = document.getElementById('modalReserva');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    mostrarPaso(1);
    document.getElementById('btnEnviar').textContent = 'Reservar';
}

document.getElementById('cerrarModal')?.addEventListener('click', cerrarModal);

document.getElementById('modalReserva')?.addEventListener('click', (e) => {
    if (e.target === e.currentTarget) cerrarModal();
});

// Cerrar modal de detalles
document.getElementById('btnCerrarInfoReserva')?.addEventListener('click', () => {
    document.getElementById('modalInfoReserva')?.classList.remove('show');
});

// Mostrar detalles de la reserva
function mostrarInfoReservaHtml(html) {
    const content = document.getElementById('reservaInfoContent');
    const modal = document.getElementById('modalInfoReserva');

    if (!content || !modal) return;

    content.innerHTML = html;
            html += `
        <div class="flex justify-end gap-2 mt-4">
            <button id="btnEditarReserva" data-id="${reservaId}" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm">Editar</button>
            <button id="btnEliminarReserva" data-id="${reservaId}" class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700 text-sm">Eliminar</button>
        </div>`;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}


// FullCalendar y eventos
window.addEventListener('DOMContentLoaded', function () {
    document.getElementById('modalReserva')?.classList.add('hidden');
    document.getElementById('modalInfoReserva')?.classList.add('hidden');

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

        events: eventosUrl,

        dateClick: function (info) {
            const clickedDate = info.dateStr;
            window.dispatchEvent(new CustomEvent('abrir-modal-reserva', {
                detail: { fecha: clickedDate }
            }));
        },
        eventClick: function (info) {
            const reservaId = info.event.id;
            const content = document.getElementById('reservaInfoContent');
            const modal = document.getElementById('modalInfoReserva');

            if (!content || !modal) return alert('Contenedor de detalles no encontrado.');

            content.innerHTML = '<p>Cargando detalles...</p>';
            modal.classList.add('show');

            fetch('/reservas/' + reservaId)
                .then(res => res.ok ? res.json() : Promise.reject(res))
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

                    // Agregar botones
                    html += `
                        <div class="flex justify-end gap-2 mt-4">
                            <button id="btnEditarReserva" class="bg-yellow-400 text-black px-4 py-1 rounded hover:bg-yellow-500 text-sm">Editar</button>
                            <button id="btnEliminarReserva" class="bg-red-500 text-white px-4 py-1 rounded hover:bg-red-600 text-sm">Eliminar</button>
                        </div>`;

                    content.innerHTML = html;

                    // Eliminar
                    document.getElementById('btnEliminarReserva')?.addEventListener('click', () => {
                        if (!confirm('¿Estás seguro de que deseas eliminar esta reserva?')) return;

                        fetch(`/reservas/${reservaId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(res => res.ok ? res.json() : Promise.reject(res))
                        .then(() => {
                            alert('✅ Reserva eliminada con éxito');
                            modal.classList.remove('show');
                            location.reload();
                        })
                        .catch(err => {
                            console.error('❌ Error al eliminar:', err);
                            alert('Error al eliminar la reserva.');
                        });
                    });

                    // Editar
                    document.getElementById('btnEditarReserva')?.addEventListener('click', () => {
                        modal.classList.remove('show');

                        // llenar formulario
                    const espacioSelect = document.getElementById('campoEspacio');
                        espacioSelect.value = data.espacio_id || 'Otro';
                        espacioSelect.dispatchEvent(new Event('change'));


                        document.getElementById('otroEspacio').value = data.otro_espacio || '';
                        document.getElementById('campoFecha').value = data.fecha || '';
                        document.getElementById('campoHoraInicio').value = data.hora_inicio?.substring(0, 5) || '';
                        document.getElementById('campoHoraFinal').value = data.hora_fin?.substring(0, 5) || '';
                        document.getElementById('campoActividad').value = data.nombre_actividad || '';
                        document.getElementById('campoPrograma').value = data.programa_evento || '';

                        const campoPersonas = document.getElementById('campoPersonas');
                        const existeOpcion = [...campoPersonas.options].some(opt => opt.value == data.num_personas);
                        if (!existeOpcion && data.num_personas) {
                            const nuevaOpcion = document.createElement('option');
                            nuevaOpcion.value = data.num_personas;
                            nuevaOpcion.text = data.num_personas;
                            campoPersonas.appendChild(nuevaOpcion);
                        }
                        campoPersonas.value = data.num_personas || '';


                        // Limpiar requerimientos
                        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                        document.querySelectorAll('select').forEach(s => s.classList.add('hidden'));

                        if (Array.isArray(data.requerimientos)) {
                            data.requerimientos.forEach(req => {
                                const checkbox = [...document.querySelectorAll(`input[type="checkbox"][value="${req.descripcion}"]`)]
                                    .find(cb => cb.name.includes(req.tipo));
                                if (checkbox) {
                                    checkbox.checked = true;
                                    const cantidad = checkbox.parentElement.querySelector('select, input[type="number"]');
                                    if (cantidad) {
                                        cantidad.classList.remove('hidden');
                                        cantidad.value = req.cantidad || 1;
                                    }
                                }
                            });
                        }

                        const modalForm = document.getElementById('modalReserva');
                        modalForm.classList.remove('hidden');
                        modalForm.classList.add('flex');
                        mostrarPaso(1);
                        document.getElementById('btnEnviar').textContent = 'Actualizar';

                        window.reservaEditandoId = reservaId;
                    });
                })
                .catch(error => {
                    content.innerHTML = `<p class="text-red-600">Error al cargar: ${error.message}</p>`;
                });
        }



    });

    calendar.render();

    document.getElementById('campoEspacio')?.addEventListener('change', function () {
        const otro = document.getElementById('otroEspacio');
        if (!otro) return;

        if (this.value === 'Otro') {
            otro.disabled = false;
            otro.classList.remove('opacity-50');
        } else {
            otro.disabled = true;
            otro.value = '';
            otro.classList.add('opacity-50');
        }
    });

    setTimeout(() => calendar?.updateSize(), 250);
});


// Mostrar u ocultar input de cantidad según checkbox
function toggleCantidad(checkbox) {
    const parent = checkbox.parentElement;
    const inputCantidad = parent.querySelector('input[type="number"]');
    const selectCantidad = parent.querySelector('select');

    if (inputCantidad) {
        inputCantidad.classList.toggle('hidden', !checkbox.checked);
    }
    if (selectCantidad) {
        selectCantidad.classList.toggle('hidden', !checkbox.checked);
    }
}

function toggleOtro(tipo) {
    const div = document.getElementById(`otro_${tipo}`);
    if (div) {
        div.classList.toggle('hidden');
    }
}

// 🧠 Escucha todos los cambios en checkboxes
document.addEventListener('change', function (e) {
    if (e.target.matches('input[type="checkbox"]')) {
        const checkbox = e.target;
        toggleCantidad(checkbox);

        if (checkbox.value === 'Otro') {
            const tipo = checkbox.name.includes('servicios_generales') ? 'servicios' :
                         checkbox.name.includes('audiovisuales') ? 'audiovisuales' :
                         checkbox.name.includes('comunicaciones') ? 'comunicaciones' :
                         checkbox.name.includes('administracion') ? 'administracion' : null;
            if (tipo) toggleOtro(tipo);
        }
    }
});
