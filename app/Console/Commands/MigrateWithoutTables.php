<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateWithoutTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:without {--exclude=* : The tables to exclude}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations while excluding specific tables';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $excludedTables = $this->option('exclude');

        // Fetch migration files
        $migrationsPath = database_path('migrations');
        $migrations = collect(scandir($migrationsPath))
            ->filter(fn($file) => str_ends_with($file, '.php'))
            ->map(fn($file) => $migrationsPath . '/' . $file);

        foreach ($migrations as $migration) {
            $migrationName = pathinfo($migration, PATHINFO_FILENAME);

            foreach ($excludedTables as $excluded) {
                if (str_contains($migrationName, $excluded)) {
                    $this->info("Skipping migration: $migrationName");
                    continue 2;
                }
            }

            $this->call('migrate', ['--path' => $migration]);
        }

        return Command::SUCCESS;
    }
}
