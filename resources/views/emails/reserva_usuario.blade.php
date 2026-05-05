<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de tu reserva</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>¡Hola {{ $reserva->usuario->name ?? 'usuario' }}!</h2>

    <p>Tu reserva se ha registrado exitosamente en el Sistema de Reservas del Colegio Americano de Cali.</p>

    <h3>Detalles de la reserva:</h3>
    <ul>
        <li><strong>Fecha:</strong> {{ $reserva->fecha }}</li>
        <li><strong>Hora de inicio:</strong> {{ $reserva->hora_inicio }}</li>
        <li><strong>Hora de fin:</strong> {{ $reserva->hora_fin }}</li>
        <li><strong>Espacio:</strong> {{ $reserva->espacio->nombre ?? $reserva->otro_espacio ?? 'No especificado' }}</li>
        <li><strong>Número de personas:</strong> {{ $reserva->num_personas }}</li>
        <li><strong>Actividad:</strong> {{ $reserva->nombre_actividad }}</li>
        <li><strong>Programa del evento:</strong> {{ $reserva->programa_evento ?? 'No especificado' }}</li>
    </ul>

    @if($reserva->requerimientos->count())
        <h3>Requerimientos seleccionados:</h3>
        <ul>
            @foreach($reserva->requerimientos as $req)
                <li>{{ ucfirst($req->tipo) }}: {{ $req->descripcion }} (Cantidad: {{ $req->cantidad }})</li>
            @endforeach
        </ul>
    @endif

    <p>Recibirás una notificación adicional cuando tu reserva sea aprobada o rechazada por las áreas implicadas.</p>

    <p>Gracias por usar nuestro sistema.</p>

    <hr>
    <p style="font-size: 12px; color: #777;">Colegio Americano de Cali - Sistema de Reservas</p>
</body>
</html>
