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
    if (!Schema::hasTable('sem_reservas')) {
        Schema::create('sem_reservas', function (Blueprint $table) {
            $table->id();
            $table->string('estado')->default('pendiente');
            $table->foreignId('usuario_id')->constrained();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('nombre_actividad');
            $table->integer('num_personas');
            $table->text('programa_evento')->nullable();
            $table->foreignId('espacio_id')->nullable()->constrained('sem_espacios');
            $table->string('otro_espacio')->nullable();
            $table->timestamps();
        });
    }
}

public function down()
{
    Schema::dropIfExists('sem_reservas');
}
};
