<!DOCTYPE html>
<html>
<head>
    <title>Reserva Confirmada</title>
</head>
<body>
    <h1>¡Reserva Confirmada!</h1>
    <p>Hola {{ $usuario->nombre }},</p>
    <p>Tu reserva para el espacio <strong>{{ $reserva->espacio->nombre }}</strong> ha sido confirmada.</p>
    
    <h2>Detalles:</h2>
    <ul>
        <li>Fecha: {{ $reserva->fecha }}</li>
        <li>Hora: {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</li>
        <li>Código: {{ $reserva->codigo }}</li>
    </ul>
    
    <p>Gracias por usar nuestro servicio,</p>
    <p>El equipo de Reservas</p>
</body>
</html>