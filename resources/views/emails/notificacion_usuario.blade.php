<!DOCTYPE html>
<html>
<head>
    <title>Pre Reserva en Espera</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1e40af; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { padding: 20px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
        .detail { margin-bottom: 10px; }
        .detail strong { display: inline-block; width: 150px; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
        .requerimientos { margin-top: 15px; }
        .requerimiento-item { margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Pre Reserva en Espera</h2>
        </div>
        
        <div class="content">
            <p>Hola {{ $reserva->usuario->name }},</p>
            <p>Tu pre reserva ha sido registrada exitosamente.</p>
            <p>Por favor espera la respuesta de aprobacion de tu reserva.</p>
            <p>Aquí están los detalles de tu reserva:</p>
            
            <div class="details">
                <div class="detail"><strong>Actividad:</strong> {{ $reserva->nombre_actividad }}</div>
                <div class="detail"><strong>Fecha:</strong> {{ $reserva->fecha }}</div>
                <div class="detail"><strong>Horario:</strong> {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</div>
                <div class="detail"><strong>Espacio:</strong> {{ $reserva->espacio->nombre ?? $reserva->otro_espacio }}</div>
                <div class="detail"><strong>Número de personas:</strong> {{ $reserva->num_personas }}</div>
            </div>

            @if($reserva->requerimientos->count() > 0)
                <div class="requerimientos">
                    <h3>Requerimientos solicitados:</h3>
                    <ul>
                        @foreach($reserva->requerimientos as $req)
                            <li class="requerimiento-item">
                                <strong>{{ ucfirst($req->tipo) }}:</strong> 
                                {{ $req->descripcion }}
                                @if($req->cantidad > 1)
                                    (Cantidad: {{ $req->cantidad }})
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p>Si necesitas hacer algún cambio, por favor contacta al administrador del sistema.</p>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
            <p>Sistema de Reservas - Colamer</p>
        </div>
    </div>
</body>
</html>