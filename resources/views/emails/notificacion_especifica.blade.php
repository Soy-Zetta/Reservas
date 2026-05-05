<!DOCTYPE html>
<html>
<head>
    <title>Requerimiento de {{ ucfirst($tipoRequerimiento) }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        h2 { color: #2c3e50; }
        .requerimiento-item { margin-left: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Requerimiento de {{ ucfirst($tipoRequerimiento) }}</h2>
        <p><strong>Reserva #:</strong> {{ $reserva->id }}</p>
        <p><strong>Actividad:</strong> {{ $reserva->nombre_actividad }}</p>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</p>
        <p><strong>Horario:</strong> {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</p>
    </div>

    <div class="card">
        <h3>Requerimientos solicitados:</h3>
        @foreach($requerimientosFiltrados as $tipo => $items)
            <div class="requerimiento-section">
                <h4>{{ ucfirst($tipo) }}:</h4>
                <ul class="requerimiento-item">
                    @foreach($items as $item)
                        <li>{{ $item->descripcion }} 
                            @if($item->cantidad > 1)
                                (x{{ $item->cantidad }})
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</body>
</html>