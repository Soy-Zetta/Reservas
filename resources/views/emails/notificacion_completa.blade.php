<!DOCTYPE html>
<html>
<head>
    <title>Reserva #{{ $reserva->id }} - Detalles completos</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 20px; background: #f9f9f9; }
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        h3 { color: #2980b9; margin-top: 20px; }
        .requerimiento-section { margin-bottom: 15px; }
        .requerimiento-title { font-weight: bold; color: #16a085; margin-bottom: 5px; }
        .requerimiento-item { margin-left: 20px; margin-bottom: 5px; }
        .requerimiento-desc { display: inline-block; width: 70%; }
        .requerimiento-cantidad { display: inline-block; width: 20%; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Reserva #{{ $reserva->id }}</h2>
        
        <p><strong>Actividad:</strong> {{ $reserva->nombre_actividad }}</p>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</p>
        <p><strong>Horario:</strong> {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</p>
        <p><strong>Espacio:</strong> {{ $reserva->espacio->nombre ?? $reserva->otro_espacio ?? 'No especificado' }}</p>
        <p><strong>Solicitante:</strong> {{ $reserva->usuario->name }} ({{ $reserva->usuario->email }})</p>
        @if($reserva->programa_evento)
            <p><strong>Programa:</strong><br>{{ $reserva->programa_evento }}</p>
        @endif
    </div>

<div class="card">
    <h3>Requerimientos Solicitados:</h3>
    
    @foreach(['audiovisuales', 'servicios_generales', 'comunicaciones', 'administracion'] as $tipo)
        @if($requerimientosAgrupados->has($tipo))
            <div class="requerimiento-group">
                <h4>{{ ucfirst(str_replace('_', ' ', $tipo)) }}:</h4>
                <ul>
                    @foreach($requerimientosAgrupados[$tipo] as $req)
                        <li>{{ $req->descripcion }} (x{{ $req->cantidad }})</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach
</div>



<!-- 🟢 SECCIÓN DE BOTONES DE APROBACIÓN -->
    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0; text-align: center;">
        <h3 style="color: #2c3e50;">Gestión de la Reserva</h3>
        <p style="margin-bottom: 20px;">Por favor, confirma la disponibilidad de los servicios correspondientes a tu área (<strong>{{ ucfirst(str_replace('_', ' ', $tipoArea)) }}</strong>):</p>
        
        <a href="{{ URL::signedRoute('reservas.aprobar', ['reserva' => $reserva->id, 'tipo' => $tipoArea]) }}" 
           style="background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 15px; display: inline-block; font-weight: bold;">
           ✅ Aprobar Servicios
        </a>
        
        <a href="{{ URL::signedRoute('reservas.rechazar', ['reserva' => $reserva->id, 'tipo' => $tipoArea]) }}" 
           style="background-color: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
           ❌ Rechazar Servicios
        </a>
    </div>
</body>
</html>



</body>
</html>