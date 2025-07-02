<?php

// En tu controlador de reservas (BookingController.php)
use Illuminate\Support\Facades\Mail;
use App\Mail\NewBookingAdminNotification;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $booking = Booking::create($request->all());
        
        // Obtener admins seleccionados (ej: [1, 3] desde formulario)
        $selectedAdminIds = $request->input('admins'); 
        $admins = User::whereIn('id', $selectedAdminIds)->where('is_admin', true)->get();

        // Enviar email a cada admin
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new NewBookingAdminNotification($booking));
        }

        return redirect()->back()->with('success', 'Reserva creada!');
    }
}
?>