<!DOCTYPE html>
<html>
<head>
    <title>Nueva Reserva Pendiente</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 15px; text-align: center; }
        .content { padding: 20px; background-color: #ffffff; border: 1px solid #dee2e6; }
        .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #6c757d; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; 
               color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nueva Reserva Pendiente</h2>
        </div>
        
        <div class="content">
            <p>Hola administrador,</p>
            <p>Se ha creado una nueva reserva que requiere tu revisión:</p>
            
            <ul>
                <li><strong>Reserva ID:</strong> #{{ $reserva->id }}</li>
                <li><strong>Usuario:</strong> {{ $usuario->name }} ({{ $usuario->email }})</li>
                <li><strong>Espacio:</strong> {{ $reserva->espacio->nombre }}</li>
                <li><strong>Fecha:</strong> {{ $reserva->fecha->format('d/m/Y') }}</li>
                <li><strong>Hora de inicio:</strong> {{ $reserva->hora_inicio }}</li>
                <li><strong>Hora de fin:</strong> {{ $reserva->hora_fin }}</li>
                <li><strong>Solicitado el:</strong> {{ $reserva->created_at->format('d/m/Y H:i') }}</li>
            </ul>
            
            <p>Por favor, revisa la reserva en el panel de administración para aprobarla o rechazarla.</p>
            
            <p>
                <a href="{{ route('admin.reservas.show', $reserva->id) }}" class="btn">
                    Ver Reserva en el Panel
                </a>
            </p>
        </div>
        
        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>