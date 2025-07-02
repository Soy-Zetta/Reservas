@extends('adminlte::page')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gestión de Reservas</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Actividad</th>
                        <th>Fecha/Hora</th>
                        <th>Espacio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservas as $reserva)
                    <tr>
                        <td>{{ $reserva->id }}</td>
                        <td>{{ $reserva->user->name }}</td>
                        <td>{{ $reserva->nombre_actividad }}</td>
                        <td>{{ $reserva->fecha }} {{ $reserva->hora_inicio }}</td>
                        <td>{{ $reserva->espacio->nombre ?? $reserva->otro_espacio }}</td>
                        <td>
                            <span class="badge 
                                @if($reserva->estado == 'aprobado') bg-success 
                                @elseif($reserva->estado == 'rechazado') bg-danger 
                                @else bg-warning @endif">
                                {{ ucfirst($reserva->estado) }}
                            </span>
                        </td>
                        <td>
                            @if($reserva->estado == 'pendiente')
                                <a href="{{ route('reservas.aprobar', $reserva->id) }}" 
                                   class="btn btn-sm btn-success">
                                   Aprobar
                                </a>
                                <button class="btn btn-sm btn-danger" 
                                        data-toggle="modal" 
                                        data-target="#rechazarModal"
                                        data-id="{{ $reserva->id }}">
                                    Rechazar
                                </button>
                            @endif
                            <a href="{{ route('reservas.show', $reserva->id) }}" 
                               class="btn btn-sm btn-info">
                               Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Rechazar -->
<div class="modal fade" id="rechazarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="rechazarForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Rechazar Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="reserva_id" id="reserva_id">
                    <div class="form-group">
                        <label for="razon">Razón del Rechazo (Opcional)</label>
                        <textarea class="form-control" name="razon" id="razon" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('#rechazarModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var reservaId = button.data('id');
        var modal = $(this);
        modal.find('#reserva_id').val(reservaId);
        modal.find('#rechazarForm').attr('action', '/reservas/' + reservaId + '/rechazar');
    });
});
</script>
@endsection