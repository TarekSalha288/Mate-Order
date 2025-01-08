<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshWithoutTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:refresh-without {--exclude=* : The tables to exclude from refresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh migrations while excluding specific tables';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $excludedTables = $this->option('exclude');

        // Rollback migrations
        $this->info('Rolling back migrations...');
        $this->call('migrate:reset', ['--force' => true]);

        // Manually drop all tables except excluded ones
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            if (!in_array($table, $excludedTables)) {
                Schema::drop($table);
                $this->info("Dropped table: $table");
            }
        }

        // Reapply migrations
        $this->info('Re-applying migrations...');
        $this->call('migrate', ['--force' => true]);

        $this->info('Migration refresh completed, excluding specified tables.');
        return Command::SUCCESS;
    }
}
