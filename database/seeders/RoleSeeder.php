<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);

        // Crear permisos
        $permissions = [
            'Gestión Usuarios',
            'Listado Usuarios',
            'Crear Usuarios',
            'Editar Usuarios',
            'Eliminar Usuarios',
            'Gestión Roles'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Asignar TODOS los permisos al rol admin
        $admin->givePermissionTo(Permission::all());
        
        // Opcional: Si quieres asegurarte que el rol usuario no tenga estos permisos
        $user->syncPermissions([]); // Esto limpia cualquier permiso asignado
    }
}