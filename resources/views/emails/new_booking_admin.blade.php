<!DOCTYPE html>
<html>
<body>
    <h2>Nueva Pre-Reserva #{{ $booking->id }}</h2>
    <p><strong>Usuario:</strong> {{ $booking->user->name }}</p>
    <p><strong>Fecha/Hora:</strong> {{ $booking->date }} {{ $booking->time }}</p>
    <p><strong>Espacio:</strong> {{ $booking->space->name }}</p>
    <p><strong>Contacto:</strong> {{ $booking->user->email }} | {{ $booking->user->phone }}</p>
    
    <a href="{{ route('admin.bookings.show', $booking->id) }}">
        Ver detalles en el panel
    </a>
</body>
</html>