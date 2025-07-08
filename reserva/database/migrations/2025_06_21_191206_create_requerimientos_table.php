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
    if (!Schema::hasTable('sem_requerimientos')) {
        Schema::create('sem_requerimientos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('departamento', [
                'informatica',
                'comunicaciones',
                'servicios_generales',
                'administracion'
            ]);
            $table->timestamps();
        });
    }
}

public function down()
{
    Schema::dropIfExists('sem_requerimientos');
}
};
