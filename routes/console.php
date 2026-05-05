<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // 🟢 Importamos el Schedule

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 🟢 PROGRAMAMOS EL COMANDO
// 'hourly()' hace que revise cada hora. 
// 'weekdays()' asegura que el celador solo trabaje de Lunes a Viernes.
Schedule::command('reservas:auto-aprobar')->hourly()->weekdays();