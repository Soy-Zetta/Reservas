@component('mail::message')
# Nueva Reserva Completa

**Actividad:** {{ $reserva->nombre_actividad }}  
**Fecha:** {{ $reserva->fecha }}  
**Hora:** {{ $reserva->hora_inicio }} a {{ $reserva->hora_fin }}  
**Personas:** {{ $reserva->num_personas }}  
**Solicitante:** {{ $reserva->usuario->name }}  

## Todos los requerimientos:
@foreach($requerimientos as $req)
@php $config = config('requirements')[$req]; @endphp
- {{ $config['label'] }} ({{ ucfirst($config['department']) }})
@endforeach

@component('mail::button', ['url' => route('reservas.show', $reserva->id)])
Ver detalles completos
@endcomponent

Gracias,  
{{ config('app.name') }}
@endcomponent