<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Rechazada</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; max-width: 500px; border-top: 5px solid #dc3545; }
        h1 { color: #dc3545; margin-bottom: 10px; }
        p { color: #555; font-size: 16px; line-height: 1.5; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn:hover { background-color: #2980b9; }
    </style>
</head>
<body>
    <div class="card">
        <h1>❌ Reserva Rechazada</h1>
        <p>Has rechazado la reserva para la actividad: <br><strong>{{ $reserva->nombre_actividad }}</strong>.</p>
        <p>Se ha notificado al usuario solicitante sobre esta decisión.</p>
        <a href="{{ url('/dashboard') }}" class="btn">Volver al Panel</a>
    </div>
</body>
</html>