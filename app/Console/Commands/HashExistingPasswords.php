<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;

class HashExistingPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passwords:hash'; // Puedes llamar al comando como quieras, este es un buen nombre

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hashes existing plain text passwords in the users table.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting password hashing...');

        // Obtener todos los usuarios
        $users = User::all();

        $count = 0;
        foreach ($users as $user) {
            // Solo hashear si la contraseña no parece ya hasheada (por ejemplo, no empieza con $2y$)
            // Esta es una verificación básica para evitar doble hasheo si ya has probado antes.
            // Sin embargo, la verificación más segura es Hash::needsRehash() si estás seguro de que el formato actual es el esperado.
             if (!Hash::needsRehash($user->password)) {
                 $this->info("Password for user ID {$user->id} ({$user->email}) seems already hashed. Skipping.");
                 continue;
             }


            // Obtener la contraseña actual (que es la cédula)
            $plainPassword = $user->password;

            // Hashear la contraseña
            $hashedPassword = Hash::make($plainPassword);

            // Actualizar el usuario con la contraseña hasheada
            $user->password = $hashedPassword;
            $user->save();

            $this->info("Password for user ID {$user->id} ({$user->email}) hashed successfully.");
            $count++;
        }

        $this->info("Hashing complete. {$count} passwords were hashed.");

        return 0;
    }
}