<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider; // Asegúrate de que esta línea esté presente

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Aquí puedes registrar bindings del contenedor de servicios
        // Por defecto, suele estar vacío.
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Aquí puedes iniciar servicios o configurar cosas
        // como esquemas de base de datos o directivas de Blade personalizadas.
        // Por defecto, suele estar vacío.
    }
}