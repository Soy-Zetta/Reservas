<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservaRequerimientoTable extends Migration
{
    public function up()
    {
        Schema::create('reserva_requerimiento', function (Blueprint $table) {
            $table->foreignId('reserva_id')->constrained()->onDelete('cascade');
            $table->foreignId('requerimiento_id')->constrained()->onDelete('cascade');
            $table->primary(['reserva_id', 'requerimiento_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reserva_requerimiento');
    }
}