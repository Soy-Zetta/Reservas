<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    if (!Schema::hasTable('sem_reserva_requerimiento')) {
        Schema::create('sem_reserva_requerimiento', function (Blueprint $table) {
            $table->foreignId('reserva_id')->constrained('sem_reservas')->onDelete('cascade');
            $table->foreignId('requerimiento_id')->constrained('sem_requerimientos')->onDelete('cascade');
            $table->primary(['reserva_id', 'requerimiento_id']);
        });
    }
}

public function down()
{
    Schema::dropIfExists('sem_reserva_requerimiento');
}
};
