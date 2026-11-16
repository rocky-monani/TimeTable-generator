<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $databaseName = DB::connection()->getDatabaseName();

        // 1. Get all foreign keys from the database schema
        $foreignKeys = DB::select("
            SELECT TABLE_NAME, CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$databaseName]);

        // 2. Loop through and drop each foreign key
        foreach ($foreignKeys as $fk) {
            $table = $fk->TABLE_NAME;
            $constraint = $fk->CONSTRAINT_NAME;

            // Execute raw statement to drop the foreign key
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversing is not possible dynamically since foreign keys are lost.
    }
};
