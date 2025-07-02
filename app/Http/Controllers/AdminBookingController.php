<?php

// En AdminBookingController.php
use App\Mail\BookingStatusUserNotification;

class AdminBookingController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->status = $request->input('status');
        $booking->rejection_reason = $request->input('rejection_reason');
        $booking->save();

        // Enviar email al usuario
        Mail::to($booking->user->email)->send(
            new BookingStatusUserNotification($booking)
        );

        return redirect()->back()->with('success', 'Estado actualizado!');
    }
}