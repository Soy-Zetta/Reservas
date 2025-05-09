<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate; // Asegúrate de que esta línea esté presente
use App\Models\User; // Asegúrate de que esta línea esté presente

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Aquí irían tus Policies si defines alguna, por ahora lo dejamos así
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // --- Definición de Gates (Puertas) ---

        // Gate para acciones que solo Admin y Superadmin pueden hacer (Ej: gestionar todas las reservas, editar/eliminar)
        Gate::define('manage-all-reservations', function (User $user) {
            // Un usuario puede gestionar todas las reservas si tiene el rol 'Admin' O el rol 'Superadmin' (usando Spatie)
            return $user->hasRole('Admin') || $user->hasRole('Superadmin');
        });

        // Gate para acciones que solo Superadmin puede hacer (Ej: gestionar usuarios)
        Gate::define('manage-users', function (User $user) {
            // Un usuario puede gestionar usuarios si tiene el rol 'Superadmin' (usando Spatie)
            return $user->hasRole('Superadmin');
        });

        // Puedes añadir más definiciones de Gates aquí si las necesitas
    }
}