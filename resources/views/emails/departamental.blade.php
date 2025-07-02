
@component('mail::message')
# Reserva para su departamento

**Actividad:** {{ $reserva->nombre_actividad }}  
**Fecha:** {{ $reserva->fecha }}  
**Hora:** {{ $reserva->hora_inicio }} a {{ $reserva->hora_fin }}  
**Solicitante:** {{ $reserva->usuario->name }}  

## Requerimientos asignados a su área:
@foreach($requerimientos as $req)
@php $config = config('requirements')[$req]; @endphp
@if($config['department'] == $departamentosRelevantes[0])
- {{ $config['label'] }}
@endif
@endforeach

@component('mail::button', ['url' => route('reservas.show', $reserva->id)])
Ver reserva completa
@endcomponent

Gracias,  
{{ config('app.name') }}
@endcomponent
<?