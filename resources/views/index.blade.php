@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="text-center">Calendario de Reservas</h1>

    <div class="text-center my-4">
    <a href="{{ route('reservas.calendario') }}" class="btn btn-primary">
        Ver Calendario de Reservas
    </a>
</div>

    <!-- Calendario -->
    <div id="calendar"></div>
</div>

    <!-- Modal para el formulario de reserva -->
    <div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReservaLabel">Nueva Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  
                </div>
            </div>
        </div>
    </div>
@endsection


       

