<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Pre-reserva - {{ ucfirst($area) }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; color: #1a202c; padding: 20px;">
    <h2 style="color: #004085;">Solicitud de Pre-reserva - Área de {{ ucfirst($area) }}</h2>

    <p>Estimado equipo de <strong>{{ ucfirst($area) }}</strong>,</p>

    <p>Se ha registrado una nueva <strong>solicitud de pre-reserva</strong> que requiere su revisión y posible aprobación.</p>

    <hr>
    <h3 style="color: #004085;">Detalles de la solicitud:</h3>
    <ul>
        <li><strong>Solicitante:</strong> {{ $reserva->usuario->name }} ({{ $reserva->usuario->email }})</li>
        <li><strong>Actividad:</strong> {{ $reserva->nombre_actividad }}</li>
        <li><strong>Fecha:</strong> {{ $reserva->fecha }}</li>
        <li><strong>Hora de inicio:</strong> {{ $reserva->hora_inicio }}</li>
        <li><strong>Hora de fin:</strong> {{ $reserva->hora_fin }}</li>
        <li><strong>Espacio:</strong> {{ $reserva->espacio->nombre ?? $reserva->otro_espacio }}</li>
        <li><strong>Número de personas:</strong> {{ $reserva->num_personas }}</li>
        <li><strong>Programa del evento:</strong> {{ $reserva->programa_evento ?? 'No especificado' }}</li>
    </ul>

    <h4 style="color: #004085;">Requerimientos generales:</h4>
    <ul>
        @foreach($reserva->requerimientos as $req)
            <li>{{ ucfirst($req->tipo) }}: {{ $req->descripcion }} ({{ $req->cantidad }})</li>
        @endforeach
    </ul>

    <p>Por favor, revise esta solicitud y confirme su aprobación o rechazo desde el sistema.</p>

    <p style="margin-top:20px; font-size:0.9em; color:#6c757d;">
        <em>Este correo fue enviado automáticamente por el Sistema de Reservas del Colegio Americano de Cali.</em>
    </p>

    @php
    $urlAprobar = URL::signedRoute('reservas.aprobar', ['reserva' => $reserva->id]);
    $urlRechazar = URL::signedRoute('reservas.rechazar', ['reserva' => $reserva->id]);
@endphp

<div style="margin-top: 25px;">
    <a href="{{ $urlAprobar }}"
       style="background:#1e88e5;color:white;padding:10px 18px;text-decoration:none;border-radius:6px;display:inline-block;margin-right:10px;">
       ✔ Aprobar reserva
    </a>

    <a href="{{ $urlRechazar }}"
       style="background:#e53935;color:white;padding:10px 18px;text-decoration:none;border-radius:6px;display:inline-block;">
       ✘ Rechazar reserva
    </a>
</div>

</body>
</html>
