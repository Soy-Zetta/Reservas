<!DOCTYPE html>
<html>
<head>
    <title>Nueva Reserva - Sistema de Reservas</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
        ul { padding-left: 20px; }
        .details { background: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin-bottom: 20px; }
        .requerimiento-item { margin-bottom: 8px; font-size: 14px; }
        .btn-container { margin-top: 25px; text-align: center; }
        .btn { 
            display: inline-block; 
            padding: 12px 24px; 
            text-decoration: none; 
            color: #ffffff !important; 
            border-radius: 5px; 
            font-weight: bold; 
            margin: 0 10px; 
        }
        .btn-approve { background-color: #28a745; }
        .btn-reject { background-color: #dc3545; }
        .footer { margin-top: 30px; font-size: 12px; color: #7f8c8d; text-align: center; }
    </style>
</head>
<body>
    <h2>📌 Solicitud de Reserva Registrada</h2>
    
    <div class="details">
        <p><strong>Actividad:</strong> {{ $reserva->nombre_actividad }}</p>
        <p><strong>Descripción / Programa:</strong> {{ $reserva->programa_evento ?: 'No se especificó programa' }}</p>
        <p><strong>Fecha:</strong> {{ $reserva->fecha }}</p>
        <p><strong>Horario:</strong> {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</p>
        <p><strong>Espacio:</strong> {{ $reserva->espacio->nombre ?? $reserva->otro_espacio }}</p>
    </div>

    @if($requerimientosFiltrados && $requerimientosFiltrados->count() > 0)
        <h3>📋 Requerimientos Solicitados:</h3>
        <ul>
            @foreach($requerimientosFiltrados as $req)
                <li class="requerimiento-item">
                    <strong>{{ $req->descripcion }}</strong> 
                    @if($req->cantidad > 1)
                        <span style="color: #e67e22;">(Cantidad: {{ $req->cantidad }})</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @elseif(!$tipoCorreo)
        <p><em>No se solicitaron requerimientos adicionales.</em></p>
    @endif
<hr style="border: 0; border-top: 1px solid #ecf0f1; margin: 30px 0;">

    {{-- 🟢 CONDICIONAL: Si NO es el solicitante, mostramos los botones --}}
    @if(!$esSolicitante)
        <h3>⚙️ Acción Requerida</h3>
        <p>Por favor, revisa la viabilidad de esta solicitud y emite tu respuesta haciendo clic en uno de los siguientes botones:</p>
        
      <div class="btn-container">
            <a href="{{ URL::signedRoute('reservas.aprobar', ['reserva' => $reserva->id, 'tipo' => $tipoCorreo ?? 'global']) }}" class="btn btn-approve">✅ Aprobar Servicio</a>
            
            <a href="{{ URL::signedRoute('reservas.rechazar', ['reserva' => $reserva->id, 'tipo' => $tipoCorreo ?? 'global']) }}" class="btn btn-reject">❌ Rechazar Servicio</a>
        </div>
    @else
        {{-- 🟢 Si SÍ es el solicitante, le damos un mensaje de tranquilidad --}}
        <h3 style="color: #2c3e50; text-align: center;">⏳ Solicitud en Revisión</h3>
        <p style="text-align: center;">Hemos recibido tu solicitud. Las áreas encargadas la revisarán pronto y te notificaremos cuando sea aprobada.</p>
    @endif

    <div class="footer">
        Este es un mensaje automático del Sistema de Reservas.
    </div>
</body>
</html>