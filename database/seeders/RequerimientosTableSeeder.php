<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RequerimientosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        //
        $requerimientos = [
        // Informática
        ['nombre' => 'Computador', 'departamento' => 'informatica'],
        ['nombre' => 'Cámara', 'departamento' => 'informatica'],
        ['nombre' => 'Conexión a Internet', 'departamento' => 'informatica'],
        ['nombre' => 'Pantalla para Proyección', 'departamento' => 'informatica'],
        ['nombre' => 'Pantalla (TV)', 'departamento' => 'informatica'],
        
        // Servicios Generales
        ['nombre' => 'Mesa', 'departamento' => 'servicios_generales'],
        ['nombre' => 'Mantel', 'departamento' => 'servicios_generales'],
        ['nombre' => 'Extensión eléctrica', 'departamento' => 'servicios_generales'],
        ['nombre' => 'Multitoma', 'departamento' => 'servicios_generales'],
        
        // Comunicaciones
        ['nombre' => 'Fotografía', 'departamento' => 'comunicaciones'],
        ['nombre' => 'Video', 'departamento' => 'comunicaciones'],
        
        // Administración
        ['nombre' => 'Refrigerio', 'departamento' => 'administracion'],
        ['nombre' => 'Agua', 'departamento' => 'administracion'],
        ['nombre' => 'Vasos', 'departamento' => 'administracion'],
    ];
    
    foreach ($requerimientos as $req) {
        Requerimiento::create($req);
    }
    }
}
