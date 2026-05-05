    var eventosUrl = document.querySelector('meta[name="url-events"]')?.content || '/reservas/events';

    // 🔴 Variable global para control de envío duplicado
    let isProcessing = false;

    // Función para verificar admin (existente)
    function isAdmin() {
        const metaAdmin = document.querySelector('meta[name="is-admin"]');
        return metaAdmin ? metaAdmin.content === '1' : false;
    }

   window.addEventListener('abrir-modal-reserva', async function (e) {
        console.log('✅ Se recibió el evento abrir-modal-reserva');

        // 🔴 Bloquear múltiples ejecuciones
        if (isProcessing) {
            console.log('⏳ Operación en curso, evitando duplicado...');
            return;
        }
        isProcessing = true;

        try {
            // 🔴 Deshabilitar botones (existente)
            const botonesReserva = document.querySelectorAll('#btnReservar, #btnSiguiente, #btnAnterior');
            botonesReserva.forEach(boton => {
                boton.disabled = true;
                boton.classList.add('cursor-not-allowed', 'opacity-75');
            });

            // Verificación de admin (existente)
            const getAdminStatus = () => {
                try {
                    const metaAdmin = document.querySelector('meta[name="is-admin"]');
                    const storageAdmin = localStorage.getItem('userRole') === 'admin';
                    return (metaAdmin?.content === '1') || storageAdmin;
                } catch {
                    return false;
                }
            };
            const esAdmin = getAdminStatus();

            // Validación básica de fecha (existente)
            if (!e.detail?.fecha) {
                alert('❌ No se proporcionó una fecha válida.');
                return;
            }

            // ====================================================================
            // 🟢 LÓGICA DE 5 DÍAS HÁBILES EXACTA Y A PRUEBA DE ZONAS HORARIAS
            // ====================================================================
            if (!esAdmin) {
                // 1. Extraer la fecha de forma segura (sin horas extrañas)
                const rawDate = e.detail.fecha.substring(0, 10); // Queda "YYYY-MM-DD"
                const parts = rawDate.split('-');
                const fechaSeleccionada = new Date(parts[0], parts[1] - 1, parts[2]);
                
                // 2. Obtener fecha de HOY exacta a la media noche
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);

                // 3. Bloquear inmediatamente si intentan reservar hoy o en el pasado
                if (fechaSeleccionada <= hoy) {
                    Swal.fire({
                        title: 'Fecha no válida',
                        text: 'No puedes realizar reservas en fechas pasadas o para el día de hoy.',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Entendido'
                    });
                    return; // 🛑 Detiene el proceso
                }

                // 4. Calcular días hábiles hacia el futuro
                let diasHabiles = 0;
                let temp = new Date(hoy);
                
                while (temp < fechaSeleccionada) {
                    temp.setDate(temp.getDate() + 1);
                    if (temp.getDay() !== 0 && temp.getDay() !== 6) {
                        diasHabiles++; // Sumar solo si no es Sábado (6) ni Domingo (0)
                    }
                }

                // 5. Bloquear si faltan menos de 5 días hábiles
                if (diasHabiles < 5) {
                    Swal.fire({
                        title: 'Anticipación Requerida',
                        text: 'Las reservas deben hacerse con al menos 5 días hábiles de anticipación.',
                        icon: 'warning',
                        confirmButtonColor: '#facc15',
                        confirmButtonText: 'Entendido'
                    });
                    return; // 🛑 Detiene el proceso
                }
            }
            // ====================================================================
            // FIN DE LA LÓGICA DE DÍAS HÁBILES
            // ====================================================================

            // Validación de días laborables (Lunes a Viernes) solo para no-admins (existente mejorado)
            if (!esAdmin) {
                // Usamos la misma forma segura de leer la fecha
                const dateParts = e.detail.fecha.substring(0, 10).split('-');
                const diaSemanaObj = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                const diaSemana = diaSemanaObj.getDay();
                
                if (diaSemana === 0 || diaSemana === 6) {
                    alert('❌ Las reservas solo están disponibles de lunes a viernes.');
                    return;
                }
            }

            // Validación de disponibilidad (existente intacta)
            if (!esAdmin && e.detail?.espacio_id && e.detail?.hora_inicio && e.detail?.hora_fin) {
                try {
                    // Mostrar loader
                    const loader = document.createElement('div');
                    loader.className = 'loader-spinner';
                    document.body.appendChild(loader);

                    const response = await fetch('/reservas/check-disponibilidad', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            espacio_id: e.detail.espacio_id,
                            fecha: e.detail.fecha,
                            hora_inicio: e.detail.hora_inicio,
                            hora_fin: e.detail.hora_fin
                        })
                    });
                    
                    // Remover loader
                    document.body.removeChild(loader);

                    if (!response.ok) throw new Error('Error en la respuesta');
                    
                    const data = await response.json();
                    if (!data.disponible) {
                        alert('❌ El espacio ya está reservado en el horario seleccionado.');
                        return;
                    }
                } catch (error) {
                    console.error('Error al verificar disponibilidad:', error);
                    alert('⚠️ No se pudo verificar la disponibilidad. Intente nuevamente.');
                    return;
                }
            }

            // --- Si pasa TODAS las validaciones --- (existente)
            resetearFormularioReserva();

            const modal = document.getElementById('modalReserva');
            if (!modal) {
                console.error('Modal no encontrado');
                return;
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            const inputFecha = document.getElementById('campoFecha');
            if (inputFecha) inputFecha.value = e.detail.fecha.substring(0, 10);

            mostrarPaso(1);

        } finally {
            // 🔴 Restaurar estado (existente)
            isProcessing = false;
            const botonesReserva = document.querySelectorAll('#btnReservar, #btnSiguiente, #btnAnterior');
            botonesReserva.forEach(boton => {
                boton.disabled = false;
                boton.classList.remove('cursor-not-allowed', 'opacity-75');
            });
        }
    });

    // // 🔴 Nuevo: Interceptar envío del formulario
    // document.querySelector('#formReserva')?.addEventListener('submit', async function(e) {
    //     e.preventDefault();
        
    //     if (isProcessing) return;
    //     isProcessing = true;
        
    //     try {
    //         const btnEnviar = document.querySelector('#btnEnviar');
    //         if (btnEnviar) {
    //             btnEnviar.disabled = true;
    //             btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...';
    //         }

    //         // Tu lógica de envío existente aquí...
    //         const response = await fetch('/reservas', {
    //             method: 'POST',
    //             body: new FormData(this)
    //         });

    //         const data = await response.json();
            
    //         if (data.success) {
    //             alert('✅ Reserva creada exitosamente');
    //             cerrarModalReserva();
    //         } else {
    //             alert(`❌ ${data.message || 'Error al crear reserva'}`);
    //         }
    //     } catch (error) {
    //         console.error('Error:', error);
    //         alert('⚠️ Error al procesar la reserva');
    //     } finally {
    //         isProcessing = false;
    //         const btnEnviar = document.querySelector('#btnEnviar');
    //         if (btnEnviar) {
    //             btnEnviar.disabled = false;
    //             btnEnviar.innerHTML = 'Reservar';
    //         }
    //     }
    // });


  function mostrarPaso(paso) {
        console.log('🔍 Ejecutando mostrarPaso con paso:', paso);
        pasoActual = paso;

        const paso1 = document.getElementById('formPaso1');
        const paso2 = document.getElementById('formPaso2');
        const btnAnterior = document.getElementById('btnAnterior');
        const btnSiguiente = document.getElementById('btnSiguiente');
        const btnEnviar = document.getElementById('btnEnviar');
        
        // NUEVO: Capturamos el contenedor del checkbox de confirmación
        const contenedorSinReq = document.getElementById('contenedorSinRequerimientos'); 

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

            // NUEVO: Ocultamos el checkbox porque estamos en el paso 1
            if (contenedorSinReq) {
                contenedorSinReq.classList.add('hidden');
                contenedorSinReq.style.display = 'none';
            }
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

            // NUEVO: Mostramos el checkbox porque ya estamos en el paso 2
            if (contenedorSinReq) {
                contenedorSinReq.classList.remove('hidden');
                // Usamos flex o block para que vuelva a ser visible
                contenedorSinReq.style.display = 'flex'; 
            }
        }
    }


    // Botones paso a paso
    document.getElementById('btnSiguiente')?.addEventListener('click', () => mostrarPaso(2));
    document.getElementById('btnAnterior')?.addEventListener('click', () => mostrarPaso(1));
document.getElementById('btnEnviar')?.addEventListener('click', async (e) => {
        e.preventDefault();

        // 🟢 1. VALIDACIÓN DE ESPACIO VACÍO
        const espacioId = document.getElementById('campoEspacio')?.value;
        const otroEspacio = document.getElementById('otroEspacio')?.value;
        if (!espacioId || espacioId.trim() === '') {
            alert('❌ Por favor, selecciona un espacio para tu reserva.');
            return;
        }
        if (espacioId === 'Otro' && (!otroEspacio || otroEspacio.trim() === '')) {
            alert('❌ Has seleccionado "Otro" espacio. Por favor, especifica cuál.');
            return;
        }

        // 🟢 2. NUEVA VALIDACIÓN: HORARIO COMPLETO
        const horaInicio = document.getElementById('campoHoraInicio')?.value;
        const horaFinal = document.getElementById('campoHoraFinal')?.value;
        
        if (!horaInicio || horaInicio.trim() === '' || !horaFinal || horaFinal.trim() === '') {
            Swal.fire({
                title: 'Horario incompleto',
                text: 'Por favor, asegúrate de seleccionar tanto la hora de inicio como la hora final de la actividad.',
                icon: 'warning',
                confirmButtonColor: '#facc15',
                confirmButtonText: 'Entendido'
            });
            return; // 🛑 Detiene el proceso
        }

        // 🟢 3. VALIDACIÓN: NOMBRE DE LA ACTIVIDAD
        const actividadTexto = document.getElementById('campoActividad')?.value;
        if (!actividadTexto || actividadTexto.trim() === '') {
            Swal.fire({
                title: 'Nombre de actividad requerido',
                text: 'Por favor, ingresa el nombre de la actividad en el Paso 1.',
                icon: 'warning',
                confirmButtonColor: '#facc15',
                confirmButtonText: 'Entendido'
            });
            return; // 🛑 Detiene el proceso
        }

        // 🟢 4. VALIDACIÓN QUE YA TENÍAMOS: PROGRAMA DEL EVENTO
        const programaTexto = document.getElementById('campoPrograma')?.value;
        if (!programaTexto || programaTexto.trim() === '') {
            Swal.fire({
                title: 'Información incompleta',
                text: 'Por favor, describe el programa o la actividad del evento en el Paso 1.',
                icon: 'warning',
                confirmButtonColor: '#facc15',
                confirmButtonText: 'Entendido'
            });
            return; 
        }

        // 🟢 5. VALIDACIÓN QUE YA TENÍAMOS: CAMPOS "OTRO" EN REQUERIMIENTOS
        let faltaTextoOtro = false;
        const categoriasValidacion = ['audiovisuales', 'servicios_generales', 'comunicaciones', 'administracion'];
        
        categoriasValidacion.forEach(cat => {
            const checkboxOtro = document.querySelector(`input[name="${cat}[]"][value="Otro"]`);
            if (checkboxOtro && checkboxOtro.checked) {
                const campoTexto = document.querySelector(`#otro_${cat} input[type="text"]`);
                if (!campoTexto || campoTexto.value.trim() === '') {
                    faltaTextoOtro = true;
                }
            }
        });

        if (faltaTextoOtro) {
            Swal.fire({
                title: 'Especificación requerida',
                text: 'Marcaste la opción "Otro" en los requerimientos. Por favor, escribe los detalles en el campo de texto correspondiente.',
                icon: 'warning',
                confirmButtonColor: '#facc15',
                confirmButtonText: 'Entendido'
            });
            return; 
        }

        // 🟢 6. VALIDACIÓN QUE YA TENÍAMOS: REQUERIMIENTOS GENERALES
        const requerimientosMarcados = document.querySelectorAll('#formPaso2 input[type="checkbox"]:checked');
        let contadorRequerimientos = 0;

        requerimientosMarcados.forEach(cb => {
            if (cb.id !== 'checkSinRequerimientos' && cb.value !== 'Otro') {
                contadorRequerimientos++;
            }
        });

        categoriasValidacion.forEach(cat => {
            const campoOtroTexto = document.querySelector(`#otro_${cat} input[type="text"]`);
            if (campoOtroTexto && campoOtroTexto.value.trim() !== '') {
                contadorRequerimientos++;
            }
        });

        const confirmarSinReq = document.getElementById('checkSinRequerimientos')?.checked;

        if (contadorRequerimientos === 0 && !confirmarSinReq) {
            Swal.fire({
                title: 'Requerimientos incompletos',
                text: 'Debe seleccionar al menos un requerimiento (sillas, audiovisuales, etc.) o marcar la casilla roja confirmando que la reserva es SIN requerimientos.',
                icon: 'warning',
                confirmButtonColor: '#facc15',
                confirmButtonText: 'Entendido'
            });
            return; 
        }

        // ====================================================================
        // 🟢 7. RECOLECCIÓN Y ENVÍO DE DATOS (EL RESTO QUEDA IGUAL)
        
        
        const formData = new FormData();

        formData.append('espacio_id', document.getElementById('campoEspacio')?.value || '');
        formData.append('otro_espacio', document.getElementById('otroEspacio')?.value || '');
        formData.append('fecha', document.getElementById('campoFecha')?.value || '');
        formData.append('hora_inicio', document.getElementById('campoHoraInicio')?.value || '');
        formData.append('hora_fin', document.getElementById('campoHoraFinal')?.value || '');
        formData.append('nombre_actividad', document.getElementById('campoActividad')?.value || '');
        formData.append('programa_evento', document.getElementById('campoPrograma')?.value || '');
        formData.append('num_personas', document.getElementById('campoPersonas')?.value || '');

        categoriasValidacion.forEach(cat => {
            const mapaNombres = {
                'audiovisuales': 'otro_audiovisual',
                'servicios_generales': 'otro_servicio_generales',
                'comunicaciones': 'otro_comunicacion',
                'administracion': 'otro_administracion',
            };

            const checkboxes = document.querySelectorAll(`input[name="${cat}[]"]:checked`);
            checkboxes.forEach(checkbox => {
                const valor = checkbox.value;
                const cantidadInput = checkbox.parentElement.querySelector('select, input[type="number"]');

                if (valor !== 'Otro') {
                    formData.append(`${cat}[]`, valor);
                    if (cantidadInput) {
                        formData.append(`cantidad_${cat}[${valor}]`, cantidadInput.value || '1');
                    }
                }
            });

            const otroText = document.querySelector(`#otro_${cat} input[type="text"]`);
            
            if (otroText && otroText.value.trim() !== '') {
                const nombreCampoOtro = mapaNombres[cat];
                formData.append(`${cat}[]`, 'Otro'); 
                formData.append(`cantidad_${cat}[Otro]`, '1');
                formData.append(nombreCampoOtro, otroText.value.trim());
            }
        });

        const reservaId = window.reservaEditandoId;
        const url = reservaId ? `/Reservas/public/reservas/${reservaId}` : '/Reservas/public/reservas';

        if (reservaId) {
            formData.append('_method', 'PUT'); 
        }

        const btnEnviar = document.getElementById('btnEnviar');
        btnEnviar.disabled = true;
        btnEnviar.textContent = 'Guardando y enviando correos... ⏳';
        btnEnviar.style.cursor = 'not-allowed';

        try {
            const response = await fetch(url, {
                method: 'POST', 
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json' 
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error en el servidor');
            }

            alert(reservaId ? '✅ Reserva actualizada correctamente' : '✅ Reserva creada correctamente');
            location.reload(); 
        } catch (err) {
            alert('❌ Error: ' + err.message);
            btnEnviar.disabled = false;
            btnEnviar.textContent = 'Actualizar';
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
            


    

dateClick: function(info) { 
        // --- SENSORES DE DIAGNÓSTICO ---
        console.log("--- INICIO DE DEBUG ---");
        console.log("1. Objeto info recibido:", info);
        console.log("2. dateStr:", info.dateStr, "| startStr:", info.startStr);
        
        const rawDate = info.dateStr || info.startStr || "";
        const cleanDate = rawDate.substring(0, 10); 
        console.log("3. Fecha limpia (cleanDate):", cleanDate);
        
        const parts = cleanDate.split('-');
        const fechaSeleccionada = new Date(parts[0], parts[1] - 1, parts[2]); 
        console.log("4. Fecha Seleccionada (JavaScript):", fechaSeleccionada);
        
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        console.log("5. Fecha de Hoy (JavaScript):", hoy);

        let diasHabiles = 0;
        let temp = new Date(hoy);
        
        while (temp < fechaSeleccionada) {
            temp.setDate(temp.getDate() + 1);
            if (temp.getDay() !== 0 && temp.getDay() !== 6) {
                diasHabiles++; 
            }
        }
        console.log("6. Días Hábiles Calculados:", diasHabiles);
        console.log("--- FIN DE DEBUG ---");
        // ---------------------------------

        const metaSuper = document.querySelector('meta[name="is-superadmin"]');
        const esSuper = metaSuper ? metaSuper.content === '1' : false;

        if (!esSuper && diasHabiles < 5) {
            Swal.fire({
                title: 'Anticipación Requerida',
                text: 'Las reservas deben hacerse con al menos 5 días hábiles de anticipación.',
                icon: 'warning',
                confirmButtonColor: '#facc15',
                confirmButtonText: 'Entendido'
            });
            return; 
        }

        window.dispatchEvent(new CustomEvent('abrir-modal-reserva', {
            detail: { fecha: cleanDate }
        }));
    },

    eventClick: function (info) {
        const reservaId = info.event.id;
        const content = document.getElementById('reservaInfoContent');
        const modal = document.getElementById('modalInfoReserva');

        const sinCantidadAudiovisuales = ['Conexión a Internet', 'Pantalla para Proyección', 'Video Bin', 'Sonido'];
        const sinCantidadComunicaciones = ['Fotografía', 'Video'];
        
        const metaSuper = document.querySelector('meta[name="is-superadmin"]');
        const esSuper = metaSuper ? metaSuper.content === '1' : false;
        const aprobado = info.event.extendedProps.aprobado ?? 0;

        if (!content || !modal) return alert('Contenedor de detalles no encontrado.');

        content.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p>Cargando detalles...</p></div>';
        modal.classList.add('show');

        fetch('/Reservas/public/reservas/' + reservaId)
            .then(res => res.ok ? res.json() : Promise.reject(res))
            .then(data => {
                const horaIni = data.hora_inicio?.substring(0, 5) || 'No definida';
                const horaFin = data.hora_fin?.substring(0, 5) || 'No definida';

                // 🟢 DIBUJAR HTML (Datos de la reserva)
                let estadoHtml = '<span class="badge badge-warning text-dark px-3 py-2" style="font-size: 0.9rem;"><i class="fas fa-clock mr-1"></i> Pre-reserva (Pendiente)</span>';
                if (aprobado == 1) estadoHtml = '<span class="badge badge-success px-3 py-2" style="font-size: 0.9rem;"><i class="fas fa-check mr-1"></i> Aprobada</span>';
                if (aprobado == 2) estadoHtml = '<span class="badge badge-purple px-3 py-2" style="background-color: #8b5cf6; color: white; font-size: 0.9rem;"><i class="fas fa-exclamation-triangle mr-1"></i> Conflicto de Servicios</span>';

                let html = `
                    <div class="mb-4 text-center border-bottom pb-3">${estadoHtml}</div>
                    <ul class="list-group list-group-flush mb-3 text-muted">
                        <li class="list-group-item px-0 py-2"><strong><i class="fas fa-tag text-primary mr-2"></i>Actividad:</strong> ${data.nombre_actividad || 'No disponible'}</li>
                        <li class="list-group-item px-0 py-2"><strong><i class="fas fa-calendar-day text-primary mr-2"></i>Fecha:</strong> ${data.fecha || 'No disponible'}</li>
                        <li class="list-group-item px-0 py-2"><strong><i class="fas fa-clock text-primary mr-2"></i>Horario:</strong> ${horaIni} a ${horaFin}</li>
                        <li class="list-group-item px-0 py-2"><strong><i class="fas fa-map-marker-alt text-primary mr-2"></i>Espacio:</strong> ${data.espacio?.nombre || data.otro_espacio || 'No especificado'}</li>
                        <li class="list-group-item px-0 py-2"><strong><i class="fas fa-users text-primary mr-2"></i>Personas:</strong> ${data.num_personas ?? 'No especificado'}</li>
                    </ul>
                    <p class="text-muted"><strong><i class="fas fa-align-left text-primary mr-2"></i>Programa del Evento:</strong><br><span class="ml-4">${data.programa_evento || 'No especificado'}</span></p>`;

                if (data.requerimientos?.length > 0) {
                    html += '<p class="mt-4 mb-2 text-muted border-bottom pb-1"><strong><i class="fas fa-boxes text-primary mr-2"></i>Requerimientos solicitados:</strong></p><ul class="pl-4 text-muted" style="list-style-type: square;">';
                    const requerimientosEstandar = ['Computador','Cámara','Conexión a Internet','Pantalla para Proyección','Pantalla (TV)','Video Bin','Sonido','Micrófono Alámbrico','Micrófono Inalámbrico','Mesa','Silla','Mantel','Extensión eléctrica','Multitoma','Fotografía','Video','Refrigerio','Agua','Vasos'];

                    data.requerimientos.forEach(req => {
                        const desc = (req.descripcion ?? '').toString().trim();
                        let mostrarCantidad = true;
                        const esExcepcion = 
                            (req.tipo === 'audiovisuales' && sinCantidadAudiovisuales.some(item => item.toLowerCase() === desc.toLowerCase())) ||
                            (req.tipo === 'comunicaciones' && sinCantidadComunicaciones.some(item => item.toLowerCase() === desc.toLowerCase())) ||
                            (desc.toLowerCase() === 'otro') ||
                            (!requerimientosEstandar.includes(desc));

                        if (esExcepcion) mostrarCantidad = false;

                        let cantidadVal = null;
                        if (req.hasOwnProperty('cantidad') && req.cantidad !== null && req.cantidad !== undefined) {
                            const s = String(req.cantidad).trim();
                            if (s !== '') cantidadVal = s;
                        }

                        const cantidadHtml = (mostrarCantidad && cantidadVal !== null) ? ` <span class="badge badge-info ml-1">Cant: ${cantidadVal}</span>` : '';
                        html += `<li class="mb-1">${desc || 'Sin descripción'}${cantidadHtml}</li>`;
                    });
                    html += '</ul>';
                }

                if (data.usuario) {
                    html += `<p class="mt-4 text-muted text-right text-sm"><em>Reservado por: ${data.usuario.name || 'No disponible'}</em></p>`;
                }

                // 🟢 HTML DE BOTONES CON SEGURIDAD ESTRICTA
                const idUsuarioLogueado = String(window.appUserId);
                const idDueñoReserva = String(data.usuario?.id || '');
                const esPropietario = (idUsuarioLogueado === idDueñoReserva);
                const esAdministrador = (esSuper || window.appUserRole === 'admin' || window.appUserRole === 'superadmin');

                let botonesHtml = '';
                
                if (esAdministrador) {
                    // SI ES ADMIN O SUPERADMIN: Control total
                    botonesHtml = `
                    <div class="mt-4 p-3 alert alert-info text-sm" style="border-left: 5px solid #17a2b8;">
                        ⭐ <strong>Modo Administrador:</strong> Tienes control total sobre esta reserva.
                    </div>
                    <div class="d-flex justify-content-end mt-2" style="gap: 8px;">
                        <button id="btnEditarReserva" class="btn btn-sm btn-warning font-weight-bold shadow-sm">Editar</button>
                        <button id="btnEliminarReserva" class="btn btn-sm btn-danger font-weight-bold shadow-sm">Eliminar</button>
                        <button id="btnAprobarReserva" class="btn btn-sm btn-success font-weight-bold shadow-sm">✅ Forzar Aprobación</button>
                        <button id="btnRechazarReserva" class="btn btn-sm btn-dark font-weight-bold shadow-sm">❌ Forzar Rechazo</button>
                    </div>`;
                } else {
                    // SI ES USUARIO NORMAL
                    if (esPropietario) {
                        // Es su propia reserva: Lógica original de usuario
                        if (aprobado == 0 || aprobado === null) {
                            botonesHtml = `
                            <div class="flex justify-end gap-2 mt-5 border-t pt-3">
                                <button id="btnEditarReserva" class="bg-yellow-400 text-black px-4 py-1 rounded hover:bg-yellow-500 text-sm shadow-md">Editar</button>
                                <button id="btnEliminarReserva" class="bg-red-500 text-white px-4 py-1 rounded hover:bg-red-600 text-sm shadow-md">Eliminar</button>
                            </div>`;
                        } else if (aprobado == 2) {
                            botonesHtml = `
                            <div class="mt-5 p-3 bg-purple-50 border-l-4 border-purple-500 text-sm text-purple-700 rounded">
                                ⚠️ Algunos servicios no están disponibles.
                            </div>
                            <div class="flex justify-end mt-3">
                                <button id="btnEliminarReserva" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm font-bold shadow-md">❌ Cancelar Reserva</button>
                            </div>`;
                        } else {
                            botonesHtml = `
                            <div class="mt-5 p-3 bg-gray-100 border-l-4 border-gray-400 text-sm text-gray-700 rounded">
                                🔒 Procesada. Contacta a rreservas@colamer.edu.co para cambios.
                            </div>`;
                        }
                    } else {
                        // NO es el dueño de la reserva: Bloqueo de seguridad
                        botonesHtml = `
                        <div class="mt-5 p-3 bg-gray-50 border-l-4 border-gray-300 text-sm text-gray-500 rounded text-center">
                            <i class="fas fa-eye mr-1"></i> Modo lectura. Esta reserva pertenece a otro usuario.
                        </div>`;
                    }
                }

                content.innerHTML = html + botonesHtml;

                // 🟢 LISTENERS DIRECTOS CON SWEETALERT2 (Para TODOS los IDs exactos)
                const btnAprobar = document.getElementById('btnAprobarReserva');
                const btnRechazar = document.getElementById('btnRechazarReserva');
                const btnEliminar = document.getElementById('btnEliminarReserva');
                const btnEditar = document.getElementById('btnEditarReserva');

                if (btnAprobar) {
                    btnAprobar.onclick = function() {
                        Swal.fire({
                            title: '¿Forzar Aprobación?',
                            text: "Se marcará esta reserva como aprobada definitivamente.",
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Sí, aprobar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(`/Reservas/public/reservas/${reservaId}/forzar/1`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                                .then(() => Swal.fire('Aprobada', 'Reserva procesada.', 'success').then(() => location.reload()));
                            }
                        });
                    };
                }

                if (btnRechazar) {
                    btnRechazar.onclick = function() {
                        Swal.fire({
                            title: '¿Forzar Rechazo?',
                            text: "La reserva será rechazada y desaparecerá del calendario.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#343a40',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Sí, rechazar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(`/Reservas/public/reservas/${reservaId}/forzar/-1`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                                .then(() => Swal.fire('Rechazada', 'Reserva ocultada del calendario.', 'success').then(() => location.reload()));
                            }
                        });
                    };
                }

                if (btnEliminar) {
                    btnEliminar.onclick = function() {
                        const tipoTexto = (aprobado == 1) ? 'esta reserva aprobada' : 'esta pre-reserva';
                        Swal.fire({
                            title: '¿Estás seguro?',
                            text: `Vas a eliminar definitivamente ${tipoTexto}. Esta acción no se puede deshacer.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(document.querySelector('meta[name="url-eliminar"]').content, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' }, body: JSON.stringify({ id: reservaId }) })
                                .then(res => res.json()).then(d => {
                                    if(d.success) Swal.fire('Eliminada', 'La operación fue exitosa.', 'success').then(() => location.reload());
                                    else Swal.fire('Error', d.message, 'error');
                                });
                            }
                        });
                    };
                }

                if (btnEditar) {
                    btnEditar.onclick = function() {
                        const fechaReserva = new Date(data.fecha + 'T00:00:00');
                        const hoy = new Date();
                        hoy.setHours(0, 0, 0, 0);
                        
                        let diasHabiles = 0;
                        let temp = new Date(hoy);
                        while (temp < fechaReserva) {
                            temp.setDate(temp.getDate() + 1);
                            if (temp.getDay() !== 0 && temp.getDay() !== 6) diasHabiles++;
                        }

                        if (!esSuper && diasHabiles < 5) {
                            Swal.fire('Acción denegada', 'Faltan menos de 5 días hábiles. No puedes editar.', 'warning');
                            return;
                        }

                        modal.classList.remove('show');
                        
                        const espacioSelect = document.getElementById('campoEspacio');
                        espacioSelect.value = data.espacio_id || 'Otro';
                        espacioSelect.dispatchEvent(new Event('change'));

                        document.getElementById('otroEspacio').value = data.otro_espacio || '';
                        document.getElementById('campoFecha').value = data.fecha || '';
                        document.getElementById('campoHoraInicio').value = horaIni;
                        document.getElementById('campoHoraFinal').value = horaFin;
                        document.getElementById('campoActividad').value = data.nombre_actividad || '';
                        document.getElementById('campoPrograma').value = data.programa_evento || '';

                        const campoPersonas = document.getElementById('campoPersonas');
                        const existeOpcion = [...campoPersonas.options].some(opt => opt.value == data.num_personas);
                        if (!existeOpcion && data.num_personas) {
                            const nuevaOpcion = document.createElement('option');
                            nuevaOpcion.value = data.num_personas; nuevaOpcion.text = data.num_personas;
                            campoPersonas.appendChild(nuevaOpcion);
                        }
                        campoPersonas.value = data.num_personas || '';

                        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                            const cantidadField = checkbox.parentElement.querySelector('select, input[type="text"]');
                            if (cantidadField) cantidadField.classList.add('hidden');
                        });

                        if (Array.isArray(data.requerimientos)) {
                            data.requerimientos.forEach(req => {
                                const checkboxes = [...document.querySelectorAll(`input[type="checkbox"]`)];
                                const checkbox = checkboxes.find(cb => cb.value === req.descripcion && cb.name.includes(req.tipo));
                                
                                if (checkbox) {
                                    checkbox.checked = true;
                                    const cantidad = checkbox.parentElement.querySelector('select');
                                    const ocultar = (req.tipo === 'audiovisuales' && sinCantidadAudiovisuales.includes(req.descripcion)) || 
                                                    (req.tipo === 'comunicaciones' && sinCantidadComunicaciones.includes(req.descripcion));
                                    
                                    if (cantidad && !ocultar) {
                                        cantidad.classList.remove('hidden');
                                        cantidad.value = req.cantidad || 1;
                                    }
                                } else {
                                    const otroDiv = document.getElementById(`otro_${req.tipo}`);
                                    if (otroDiv) {
                                        otroDiv.classList.remove('hidden');
                                        const inputTexto = otroDiv.querySelector('input[type="text"]');
                                        const cbOtro = otroDiv.parentElement.querySelector('input[type="checkbox"][value="Otro"]');
                                        if (cbOtro) cbOtro.checked = true;
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
                    };
                }

            })
            .catch(err => {
                content.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Error de lectura: ${err.message}</div>`;
            });
    },


        });

        


    calendar.render(); 

        // 🟢 EL TRUCO EN TIEMPO REAL (Refresco invisible)
        // Le pide a Laravel los datos nuevos cada 15 segundos sin parpadear
        setInterval(() => {
            calendar.refetchEvents();
        }, 15000); // 15000 milisegundos = 15 segundos




        

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




            // 🟢 BOTÓN PARA CERRAR EL FORMULARIO DE RESERVA
    document.getElementById('btnCerrarFormulario')?.addEventListener('click', function() {
        const modalForm = document.getElementById('modalReserva');
        modalForm.classList.add('hidden');
        modalForm.classList.remove('flex');
    });
        });