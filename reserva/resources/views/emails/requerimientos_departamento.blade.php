<!DOCTYPE html>
<html>
<head>
    <title>Requerimientos de {{ $departamento }}</title>
    <style>
        /* Estilos similares a los anteriores */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Requerimientos de {{ $departamento }}</h2>
        </div>
        
        <div class="content">
            <p>Hola administrador,</p>
            <p>Se han solicitado los siguientes requerimientos para la reserva #{{ $reserva->id }}:</p>
            
            <h3>Detalles de la reserva:</h3>
            <ul>
                <li><strong>Usuario:</strong> {{ $usuario->name }}</li>
                <li><strong>Espacio:</strong> {{ $reserva->espacio->nombre }}</li>
                <li><strong>Fecha:</strong> {{ $reserva->fecha->format('d/m/Y') }}</li>
                <li><strong>Hora:</strong> {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</li>
            </ul>
            
            <h3>Requerimientos solicitados:</h3>
            <ul>
                @foreach($requerimientos as $requerimiento)
                    <li>{{ $requerimiento->nombre }}</li>
                @endforeach
            </ul>
        </div>
        
        <div class="footer">
            <p>Este es un mensaje automático del sistema de reservas.</p>
        </div>
    </div>
</body>
</html>