<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupSQLiteAsMySQL extends Command {
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'app:backup-s-q-lite-as-my-s-q-l';

    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'Command description';

    /**
    * Execute the console command.
    */

    public function handle() {
        // Set the path for the backup file
        $backupPath = storage_path( 'database_backup_' . date( 'Y_m_d_His' ) . '.sql' );

        // Open the file for writing
        $file = fopen( $backupPath, 'w' );

        // Fetch the list of tables
        $tables = DB::select( "SELECT name FROM sqlite_master WHERE type='table'" );

        foreach ( $tables as $table ) {
            $tableName = $table->name;

            // Write CREATE TABLE statement
            $createTableStmt = DB::selectOne( "SELECT sql FROM sqlite_master WHERE type='table' AND name='$tableName'" );
            fwrite( $file, $createTableStmt->sql . ';\n' );

            // Fetch rows
            $rows = DB::table( $tableName )->get();

            foreach ( $rows as $row ) {
                $columns = implode( ', ', array_map( fn( $col ) => "`$col`", array_keys( ( array ) $row ) ) );
                $values = implode( ', ', array_map( fn( $val ) => DB::getPdo()->quote( $val ), array_values( ( array ) $row ) ) );

                // Write INSERT INTO statement
                $insertStmt = "INSERT INTO `$tableName` ($columns) VALUES ($values);\n";
                fwrite( $file, $insertStmt );
            }

            fwrite( $file, '\n' );
        }

        fclose( $file );
        echo "Backup completed: $backupPath\n";
    }
}

