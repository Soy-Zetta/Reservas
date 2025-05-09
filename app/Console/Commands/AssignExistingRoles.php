<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; // Importa tu modelo User
use Spatie\Permission\Models\Role; // Importa el modelo Role de Spatie

class AssignExistingRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:assign-existing'; // Un nombre descriptivo para el comando

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assigns roles to existing users based on their cargo field.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting role assignment for existing users...');

        // Obtener todos los usuarios
        $users = User::all();

        $superadminRoleId = 1; // El ID del Superadmin que definimos
        $adminRoleId = 2;      // El ID del Admin que definimos
        $usuarioRoleId = 3;    // El ID del Usuario que definimos

        $assignedCount = 0;

        foreach ($users as $user) {
            $roleToAssign = null;

            // --- Lógica de Asignación de Roles ---
            // Basado en la lógica que definimos anteriormente

            if ($user->id === 1) {
                // El usuario con ID 1 es el Superadmin
                $roleToAssign = 'Superadmin';
            } else {
                // Asignar roles basado en el campo cargo
                switch ($user->cargo) {
                    case 'bibliotecóloga': // Nota: Usar el nombre exacto en tu base de datos
                    case 'servicios generales': // Nota: Usar el nombre exacto
                        $roleToAssign = 'Admin';
                        break;
                    case 'maestro':
                    case 'Psicologo(a)':
                    case 'Capellania':
                    case 'coordinador':
                    case 'secretaria':
                    case 'auxiliar':
                    case 'asistente de comunicaciones':
                        $roleToAssign = 'Usuario';
                        break;
                    // Puedes añadir más casos si tienes otros cargos que deberían ser Admin o Usuario
                    default:
                         // Si el cargo no coincide con ningún rol específico
                         // Puedes decidir qué hacer aquí. Quizás asignarles el rol 'Usuario' por defecto
                         // o simplemente no asignarles ningún rol si el cargo es desconocido.
                         // Por ahora, si no tiene un cargo que mapee a un rol, no asignamos nada.
                         // Si quieres asignar 'Usuario' por defecto a todos los demás cargos, descomenta la siguiente línea:
                         // $roleToAssign = 'Usuario';
                         $this->warn("User ID {$user->id} ({$user->name}) has unrecognized cargo: '{$user->cargo}'. No specific role assigned based on cargo.");
                        break;
                }
                 // Si el usuario con ID 2 debe ser Admin, manejamos ese caso específico si no tiene cargo
                if ($user->id === 2 && is_null($user->cargo)) {
                     $roleToAssign = 'Admin';
                }

            }

            // --- Asignar el Rol Usando Spatie ---
            if ($roleToAssign) {
                // Antes de asignar, opcionalmente puedes remover roles anteriores si el usuario ya tenía alguno asignado con Spatie
                // $user->syncRoles([]); // Esto removería todos los roles actuales
                // O para más seguridad y evitar duplicados si ejecutas varias veces:
                 $user->removeRole($roleToAssign); // Remover si ya tiene este rol
                 // Ahora asignar el rol:
                $user->assignRole($roleToAssign);
                $this->info("Assigned role '{$roleToAssign}' to User ID {$user->id} ({$user->name}).");
                $assignedCount++;
            }

        }

        $this->info("Role assignment complete. {$assignedCount} users were assigned a role.");

        return 0;
    }
}