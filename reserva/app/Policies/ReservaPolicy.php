<?php

namespace App\Policies;

use App\Models\Reserva;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any reservas.
     */
    public function viewAny(User $user): bool
    {
        return true; // Por ahora, permitimos a cualquier usuario autenticado ver la lista
    }

    /**
     * Determine if the user can view the reserva.
     */
    public function view(User $user, Reserva $reserva): bool
    {
        return $user->hasRole('admin') || $user->id === $reserva->usuario_id;
    }

    /**
     * Determine if the user can create reservas.
     */
    public function create(User $user): bool
    {
        return true; // Por ahora, permitimos a cualquier usuario autenticado crear reservas
    }

    /**
     * Determine if the user can update the reserva.
     */
    public function update(User $user, Reserva $reserva): bool
    {
        if ($user->hasRole('admin')) {
            return true; // Los administradores siempre pueden editar
        }

        // Si no es administrador, solo puede editar si es el dueño y la reserva no está aprobada
        return $user->id === $reserva->usuario_id && !$reserva->aprobado;
    }

    /**
     * Determine if the user can delete the reserva.
     */
    public function delete(User $user, Reserva $reserva): bool
    {
        return $user->hasRole('admin'); // Solo los administradores pueden eliminar
    }

    /**
     * Determine if the user can manage all reservas.
     */
    public function manageAllReservations(User $user): bool
    {
        return $user->hasRole('admin'); // Asumimos que solo los administradores pueden gestionar todas
    }
}