<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
   // Schema::table('sem_users', function (Blueprint $table) {
        // Añadimos un valor por defecto temporal para registerDate si es necesario
     //   $table->timestamp('registerDate')->nullable()->default(now())->change();

       // $table->text('two_factor_secret')->nullable()->after('password');
        //$table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
        //$table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        //$table->rememberToken();
        //$table->unsignedBigInteger('current_team_id')->nullable();
        //$table->string('profile_photo_path', 2048)->nullable();
        //$table->timestamps();
    //});
}

public function down(): void
{
    //Schema::table('sem_users', function (Blueprint $table) {
     //  $table->dropColumn([
        //    'two_factor_secret',
        //    'two_factor_recovery_codes',
       //     'two_factor_confirmed_at',
       //     'current_team_id',
        //    'profile_photo_path',
      //  ]);
       // $table->dropRememberToken();
    //    $table->dropTimestamps();

        // Si hiciste registerDate nullable y quieres revertirlo
        // $table->timestamp('registerDate')->nullable(false)->change();
    //});
}
};
