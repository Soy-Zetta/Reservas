<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva Aprobada</title>
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
            color: #155724;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>✅ Reserva Aprobada</h2>

        <p>Estimado/a <strong>{{ $reserva->usuario->name }}</strong>,</p>

        <p>Nos complace informarte que tu solicitud de reserva ha sido <strong>aprobada</strong>.</p>

        <div class="info">
            <p><strong>Actividad:</strong> {{ $reserva->nombre_actividad }}</p>
            <p><strong>Fecha:</strong> {{ $reserva->fecha }}</p>
            <p><strong>Hora:</strong> {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</p>
            <p><strong>Espacio:</strong> {{ $reserva->espacio->nombre ?? $reserva->otro_espacio }}</p>
            <p><strong>Número de Personas:</strong> {{ $reserva->num_personas }}</p>
            <p><strong>Programa del Evento:</strong> {{ $reserva->programa_evento ?? 'No especificado' }}</p>

            @if ($reserva->requerimientos->count())
                <p><strong>Requerimientos solicitados:</strong></p>
                <ul>
                    @foreach ($reserva->requerimientos as $req)
                        <li>{{ ucfirst($req->tipo) }}: {{ $req->descripcion }} 
                            @if ($req->cantidad) ({{ $req->cantidad }}) @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p>No se solicitaron requerimientos adicionales.</p>
            @endif
        </div>

        <p>Gracias por utilizar nuestro sistema de reservas institucional.</p>

        <div class="footer">
            © {{ date('Y') }} Colegio Americano de Cali — Sistema de Reservas
        </div>
    </div>
</body>
</html>
