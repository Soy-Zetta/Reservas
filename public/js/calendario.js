// 📁 calendario.js
const eventosUrl = document.querySelector('meta[name="url-events"]')?.content || '/reservas/events';

// Mostrar modal de reserva
    window.addEventListener('abrir-modal-reserva', function (e) {
        console.log('✅ Se recibió el evento abrir-modal-reserva');

        const fechaSeleccionada = new Date(e.detail?.fecha); // formato: YYYY-MM-DD
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        const tresDiasDespues = new Date(hoy);
        tresDiasDespues.setDate(hoy.getDate() + 3);

        if (fechaSeleccionada < tresDiasDespues) {
            alert('❌ Las reservas deben realizarse con al menos 3 días de anticipación.');
            return; // 🔥 No abrir el formulario
        }

        resetearFormularioReserva();

        const modal = document.getElementById('modalReserva');
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (e.detail?.fecha) {
            const input = document.getElementById('campoFecha');
            if (input) input.value = e.detail.fecha;
        }

        mostrarPaso(1);
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
    const mapaNombres = {
        'audiovisuales': 'otro_audiovisual',
        'servicios_generales': 'otro_servicio_general',
        'comunicaciones': 'otro_comunicacion',
        'administracion': 'otro_administracion',
    };

    // ✅ Guardar requerimientos estándar (checkboxes marcados)
        const checkboxes = document.querySelectorAll(`input[name="${cat}[]"]:checked`);
        checkboxes.forEach(checkbox => {
            const valor = checkbox.value;
            const cantidadInput = checkbox.parentElement.querySelector('select, input[type="number"]');

            // Evitar duplicar el "Otro" si ya lo vamos a procesar después
            if (valor !== 'Otro') {
                formData.append(`${cat}[]`, valor);
                if (cantidadInput) {
                    formData.append(`cantidad_${cat}[${valor}]`, cantidadInput.value || '1');
                }
            }
        });

        // ✅ FORZAR el envío del campo "Otro" si tiene contenido, incluso si el checkbox no está marcado
        const otroText = document.querySelector(`#otro_${cat} input[type="text"]`);
        

        if (otroText && otroText.value.trim() !== '') {
            const nombreCampoOtro = mapaNombres[cat];
            formData.append(`cantidad_${cat}[Otro]`, '1');
            formData.append(nombreCampoOtro, otroText.value.trim());
           

            console.log(`✅ [FORZADO] ${cat}: Otro →`, otroText.value.trim());
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



function resetearFormularioReserva() {
    // 🔄 Paso 1 - Campos básicos
    document.getElementById('campoActividad').value = '';
    document.getElementById('campoFecha').value = '';
    document.getElementById('campoHoraInicio').value = '';
    document.getElementById('campoHoraFinal').value = '';
    document.getElementById('campoPrograma').value = '';
    document.getElementById('campoPersonas').value = '';
    document.getElementById('otroEspacio').value = '';

    const espacioSelect = document.getElementById('campoEspacio');
    if (espacioSelect) espacioSelect.value = '';

    // 🔄 Paso 2 - Limpiar checkboxes y campos "Otro"
    const categorias = ['audiovisuales', 'servicios_generales', 'comunicaciones', 'administracion'];
    categorias.forEach(cat => {
        document.querySelectorAll(`input[name="${cat}[]"]`).forEach(cb => cb.checked = false);
        const otroDiv = document.getElementById(`otro_${cat}`);
        if (otroDiv) {
            otroDiv.classList.add('hidden');
            const inputTexto = otroDiv.querySelector('input[type="text"]');
            if (inputTexto) inputTexto.value = '';
        }
    });

    // 🔄 Limpiar estado de edición y resetear botón
    window.reservaEditandoId = null;
    document.getElementById('btnEnviar').textContent = 'Reservar';
    mostrarPaso(1);
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

            // Listas de ítems que NO deben mostrar cantidad
            const sinCantidadAudiovisuales = ['Conexión a Internet', 'Pantalla para Proyección', 'Video Bin', 'Sonido'];
            const sinCantidadComunicaciones = ['Fotografía', 'Video'];

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

                        const requerimientosEstandar = [
                            // 🔹 Audiovisuales
                            'Computador', 'Cámara', 'Conexión a Internet', 'Pantalla para Proyección',
                            'Pantalla (TV)', 'Video Bin', 'Sonido', 'Micrófono',

                            // 🔹 Servicios Generales
                            'Mesa', 'Mantel', 'Extensión eléctrica', 'Multitoma',

                            // 🔹 Comunicaciones
                            'Fotografía', 'Video',

                            // 🔹 Administración
                            'Refrigerio', 'Agua', 'Vasos'
                        ];

                        data.requerimientos.forEach(req => {
                            const desc = req.descripcion?.trim();
                            const tipo = req.tipo;
                            let mostrarCantidad = true;

                            // Excepciones: no mostrar cantidad si está en las listas
                            if (tipo === 'audiovisuales' && sinCantidadAudiovisuales.some(item => item.toLowerCase() === desc?.toLowerCase())) {
                            mostrarCantidad = false;
                            }

                            if (tipo === 'comunicaciones' && sinCantidadComunicaciones.some(item => item.toLowerCase() === desc?.toLowerCase())) {
                            mostrarCantidad = false;
                            }


                                                        // Detectar si es "Otro" manual (no está en la lista y fue ingresado como texto)
                            const esOtroManual = req.descripcion && req.descripcion.toLowerCase() === 'otro';

                            // Solo mostrar cantidad si NO está en listas sin cantidad y NO es "Otro"
                            if (
                                (tipo === 'audiovisuales' && sinCantidadAudiovisuales.some(item => item.toLowerCase() === desc?.toLowerCase())) ||
                                (tipo === 'comunicaciones' && sinCantidadComunicaciones.some(item => item.toLowerCase() === desc?.toLowerCase())) ||
                                esOtroManual
                            ) {
                                mostrarCantidad = false;
                            }

                            const esOtro = desc === 'Otro' || !requerimientosEstandar.includes(desc);

                        if (
                        (tipo === 'audiovisuales' && sinCantidadAudiovisuales.includes(desc)) ||
                        (tipo === 'comunicaciones' && sinCantidadComunicaciones.includes(desc)) ||
                        esOtro
                        ) {
                        mostrarCantidad = false;
                        }

                        html += `<li>${desc}${(mostrarCantidad && req.cantidad) ? ' (Cantidad: ' + req.cantidad + ')' : ''}</li>`;


                        });

                        html += '</ul>';
                    } else {
                        html += '<p><strong>Requerimientos:</strong> No se solicitaron.</p>';
                    }

                    if (data.usuario) {
                        html += `<p><strong>Reservado por:</strong> ${data.usuario.name || 'No disponible'}</p>`;
                    }

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
                   
                        // Select de espacios
                      const espacioSelect = document.getElementById('campoEspacio');
                    espacioSelect.classList.remove('hidden'); // 👈 Esto asegura que se vea
                    espacioSelect.value = data.espacio_id || 'Otro';
                    espacioSelect.dispatchEvent(new Event('change'));
                    const campoPersonas = document.getElementById('campoPersonas');
                    espacioSelect.disabled = false; // ✅ Asegúrate de que no esté gris




                        document.getElementById('otroEspacio').value = data.otro_espacio || '';
                        document.getElementById('campoFecha').value = data.fecha || '';
                        document.getElementById('campoHoraInicio').value = data.hora_inicio?.substring(0, 5) || '';
                        document.getElementById('campoHoraFinal').value = data.hora_fin?.substring(0, 5) || '';
                        document.getElementById('campoActividad').value = data.nombre_actividad || '';
                        document.getElementById('campoPrograma').value = data.programa_evento || '';
         
         
         
            const existeOpcion = [...campoPersonas.options].some(opt => opt.value == data.num_personas);
            if (!existeOpcion && data.num_personas) {
                const nuevaOpcion = document.createElement('option');
                nuevaOpcion.value = data.num_personas;
                nuevaOpcion.text = data.num_personas;
                campoPersonas.appendChild(nuevaOpcion);
            }
            campoPersonas.value = data.num_personas || '';

                campoEspacio.classList.remove('hidden');
                campoEspacio.disabled = false;

                campoPersonas.classList.remove('hidden');
                campoPersonas.disabled = false;




                        // Limpiar requerimientos
                        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

                        // ✅ Solo ocultamos los selects de cantidad dentro de cada checkbox (no los principales)
                        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                            const cantidadField = checkbox.parentElement.querySelector('select, input[type="number"]');
                            if (cantidadField) {
                                cantidadField.classList.add('hidden');
                            }
                        });

                            if (Array.isArray(data.requerimientos)) {
                data.requerimientos.forEach(req => {
                    const checkboxes = [...document.querySelectorAll(`input[type="checkbox"]`)];
                    const checkbox = checkboxes.find(cb => cb.value === req.descripcion && cb.name.includes(req.tipo));

                    if (checkbox) {
                        checkbox.checked = true;

                        const cantidad = checkbox.parentElement.querySelector('select, input[type="number"]');
                        const tipo = req.tipo;
                        const descripcion = req.descripcion?.trim();

                        // ✅ Detectar si el ítem no debe mostrar cantidad
                        const ocultarCantidad =
                            (tipo === 'audiovisuales' && sinCantidadAudiovisuales.some(item => item.toLowerCase() === descripcion.toLowerCase())) ||
                            (tipo === 'comunicaciones' && sinCantidadComunicaciones.some(item => item.toLowerCase() === descripcion.toLowerCase()));

                        if (cantidad) {
                            if (!ocultarCantidad) {
                                cantidad.classList.remove('hidden');
                                cantidad.value = req.cantidad || 1;
                            } else {
                                cantidad.classList.add('hidden');
                            }
                        }

                    } else {
                        // ✅ Si es un campo "Otro"
                        const otroDiv = document.getElementById(`otro_${req.tipo}`);
                        if (otroDiv) {
                            otroDiv.classList.remove('hidden');

                            const inputTexto = otroDiv.querySelector('input[type="text"]');
                            const cbOtro = otroDiv.querySelector('input[type="checkbox"][value="Otro"]');

                            if (cbOtro) {
                                cbOtro.checked = true;
                                cbOtro.dataset.forzado = '1'; // Evita que se oculte automáticamente
                                cbOtro.dispatchEvent(new Event('change'));
                            }

                            if (inputTexto) inputTexto.value = req.descripcion;
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




    function toggleOtro(tipo) {
        const div = document.getElementById(`otro_${tipo}`);
        if (div) {
            div.classList.toggle('hidden');
        }
    }

    function limpiarFormularioReserva() {
    // Desmarcar todos los checkboxes
    document.querySelectorAll('#formularioPaso2 input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = false;
    });

    // Ocultar y resetear todos los selects
    document.querySelectorAll('#formularioPaso2 select').forEach(select => {
        select.selectedIndex = 0;
        select.classList.add('hidden');
    });

    // Limpiar todos los campos de texto
    document.querySelectorAll('#formularioPaso2 input[type="text"]').forEach(input => {
        input.value = '';
    });

    // Ocultar campos "Otro"
    document.querySelectorAll('#formularioPaso2 .hidden').forEach(el => {
        if (el.id?.includes('otro')) {
            el.classList.add('hidden');
        }
    });
}


    
    // ✅ Activador general para mostrar/ocultar campos de cantidad y "Otro" al marcar checkboxes
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                 const cantidad = this.parentElement.querySelector('select');

            // Detectar si este item es de una categoría con excepciones
            const nombreCategoria = this.name.split('[')[0]; // ej: 'audiovisuales'
            const valor = this.value;

            const sinCantidadAudiovisuales = ['Conexión a Internet', 'Pantalla para Proyección', 'Video Beam', 'Sonido'];
            const sinCantidadComunicaciones = ['Fotografía', 'Video'];

            let ocultarCantidad = false;

            if (nombreCategoria === 'audiovisuales' && sinCantidadAudiovisuales.includes(valor)) {
                ocultarCantidad = true;
            }

            if (nombreCategoria === 'comunicaciones' && sinCantidadComunicaciones.includes(valor)) {
                ocultarCantidad = true;
            }

            if (cantidad) {
                // Si el checkbox está marcado Y el item NO está en la lista de excepciones → mostrar
                cantidad.classList.toggle('hidden', !this.checked || ocultarCantidad);
            }


                // Mostrar u ocultar campos de texto si el valor es "Otro"
                if (checkbox.value === 'Otro') {
                    let tipo = null;
                    if (checkbox.name.includes('audiovisuales')) tipo = 'audiovisuales';
                    if (checkbox.name.includes('servicios_generales')) tipo = 'servicios_generales';
                    if (checkbox.name.includes('comunicaciones')) tipo = 'comunicaciones';
                    if (checkbox.name.includes('administracion')) tipo = 'administracion';

                    if (tipo) {
                        const divOtro = document.getElementById(`otro_${tipo}`);
                        if (divOtro) {
                            divOtro.classList.toggle('hidden', !checkbox.checked);
                        }
                    }
                }
            });
        });
    });