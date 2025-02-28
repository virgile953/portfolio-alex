<?php

require __DIR__.'/../vendor/autoload.php';

// Set up the application
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

// Configure MySQL connection
Config::set('database.connections.mysql', [
    'driver' => 'mysql',
    'host' => env('MYSQL_HOST', '127.0.0.1'),
    'port' => env('MYSQL_PORT', '3306'),
    'database' => env('MYSQL_DATABASE', 'laravel'),
    'username' => env('MYSQL_USERNAME', 'root'),
    'password' => env('MYSQL_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
]);

// The tables to be migrated
$tables = [
    'users',
    'products',
    'customers',
    'invoices',
    'invoice_product',
    // Add any other tables you need to transfer
];

// Handle each table
foreach ($tables as $table) {
    echo "Transferring data for table: $table\n";

    try {
        // Check if the table exists in MySQL
        if (!Schema::connection('mysql')->hasTable($table)) {
            echo "  Table $table does not exist in MySQL\n";
            continue;
        }

        // Check if the table exists in PostgreSQL
        if (!Schema::connection('pgsql')->hasTable($table)) {
            echo "  Table $table does not exist in PostgreSQL\n";
            continue;
        }

        // Get data from MySQL
        $records = DB::connection('mysql')->table($table)->get();

        // If table has data, transfer it to PostgreSQL
        if (count($records) > 0) {
            echo "  Found " . count($records) . " records\n";

            // Insert each record into PostgreSQL
            foreach ($records as $record) {
                $data = (array) $record;

                try {
                    DB::connection('pgsql')->table($table)->insert($data);
                } catch (\Exception $e) {
                    echo "  Error inserting record: " . $e->getMessage() . "\n";
                }
            }

            echo "  Completed transfer for table: $table\n";
        } else {
            echo "  No records found in table: $table\n";
        }
    } catch (\Exception $e) {
        echo "  Error processing table $table: " . $e->getMessage() . "\n";
    }
}

echo "Data transfer completed!\n";

$kernel->terminate(null, 0);
