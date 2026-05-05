<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva Rechazada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        h2 {
            color: #721c24;
        }
        .info {
            margin-top: 15px;
            line-height: 1.6;
        }
        .footer {
            font-size: 12px;
            color: #777;
            margin-top: 25px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            text-align: center;
        }
        .motivo {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>❌ Reserva Rechazada</h2>

        <p>Estimado/a <strong>{{ $reserva->usuario->name }}</strong>,</p>

        <p>Lamentamos informarte que tu solicitud de reserva ha sido <strong>rechazada</strong>.</p>

        <div class="info">
            <p><strong>Actividad:</strong> {{ $reserva->nombre_actividad }}</p>
            <p><strong>Fecha:</strong> {{ $reserva->fecha }}</p>
            <p><strong>Hora:</strong> {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</p>
            <p><strong>Espacio:</strong> {{ $reserva->espacio->nombre ?? $reserva->otro_espacio }}</p>

            <p class="motivo"><strong>Motivo del rechazo:</strong> {{ $motivo }}</p>
        </div>

        <p>Si consideras que esta decisión fue un error o deseas ajustar la información, puedes ingresar al sistema y realizar una nueva solicitud.</p>

        <div class="footer">
            © {{ date('Y') }} Colegio Americano de Cali — Sistema de Reservas
        </div>
    </div>
</body>
</html>
