<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB; // Importa el facade DB
use Throwable; // Importa Throwable para capturar cualquier error de conexión

class ListDatabaseTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Define cómo ejecutarás el comando en la terminal: php artisan list:database:tables
    protected $signature = 'list:database:tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all tables in the connected database.'; // Descripción del comando

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Attempting to list tables in the database:');

        try {
            // Ejecuta una consulta SQL para obtener la lista de tablas
            $tables = DB::select('SHOW TABLES');

            // Formatea los resultados (la columna con el nombre de la tabla varía segun el driver SQL)
            // Para MySQL, la columna suele ser 'Tables_in_nombre_de_la_base_de_datos'
            $dbName = DB::getDatabaseName(); // Obtiene el nombre de la base de datos actual
            $columnName = 'Tables_in_' . $dbName; // Construye el nombre esperado de la columna

            if (empty($tables)) {
                $this->info('No tables found in the database.');
                return self::SUCCESS;
            }

            $this->info('Tables found:');
            foreach ($tables as $table) {
                // Accede al nombre de la tabla usando el nombre dinámico de la columna
                // Asegúrate de que la columna esperada realmente exista en los resultados
                if (isset($table->$columnName)) {
                    $this->line('- ' . $table->$columnName);
                } else {
                     // Si el nombre de la columna es inesperado, volcar la estructura de un objeto para diagnosticar
                     $this->error("Unexpected column name in SHOW TABLES result.");
                     $this->error("Expected column: " . $columnName);
                     $this->error("Received object structure:");
                     print_r($table); // Imprime la estructura del objeto para ver qué columnas tiene
                     return self::FAILURE;
                }
            }

            $this->info('Table listing complete.');
            return self::SUCCESS;

        } catch (Throwable $e) {
            // Captura cualquier error durante la conexión o la consulta
            $this->error('Could not connect to the database or list tables.');
            $this->error('Error: ' . $e->getMessage());
            // Puedes descomentar la siguiente línea para ver la traza completa del error si es necesario
            // $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}