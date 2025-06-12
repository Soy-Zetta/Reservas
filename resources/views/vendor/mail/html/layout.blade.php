<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'Notificación' }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 30px; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 0.8em; color: #6c757d; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sistema de Reservas</h2>
    </div>
    
    <div class="content">
        {{ $slot }}
    </div>
    
    <div class="footer">
        © {{ date('Y') }} Sistema de Reservas. Todos los derechos reservados.<br>
        Este es un mensaje automático, por favor no responda a este correo.
    </div>
</body>
</html>