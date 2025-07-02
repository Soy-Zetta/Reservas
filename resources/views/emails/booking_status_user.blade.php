<!DOCTYPE html>
<html>
<body>
    <h2>Estado de tu reserva #{{ $booking->id }}</h2>
    <p>Tu reserva para <strong>{{ $booking->space->name }}</strong> ha sido:</p>
    
    @if($booking->status === 'approved')
        <h3 style="color:green;">APROBADA ✅</h3>
        <p>Detalles: {{ $booking->date }} a las {{ $booking->time }}</p>
    @else
        <h3 style="color:red;">RECHAZADA ❌</h3>
        <p><strong>Motivo:</strong> {{ $booking->rejection_reason }}</p>
    @endif
</body>
</html>