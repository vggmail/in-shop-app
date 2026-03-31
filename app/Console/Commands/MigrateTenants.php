<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {--seed : Seed the database after migrating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for all tenant databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = \App\Models\Tenant::where('is_active', 1)->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
            return;
        }

        $prefix = env('DB_PREFIX', '');

        foreach ($tenants as $tenant) {
            // Skip localhost and bare IPs
            if (in_array($tenant->subdomain, ['localhost', '127', 'www'])) {
                continue;
            }

            $dbName = $prefix . $tenant->subdomain;
            $this->info("==========================================");
            $this->info("Migrating Database for Tenant: {$tenant->name} ({$dbName})");
            $this->info("==========================================");

            // 1. Ensure the database actually exists before migrating
            try {
                \Illuminate\Support\Facades\DB::connection('mysql')->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->info("✓ Database connection established: {$dbName}");
            } catch (\Exception $e) {
                $this->error("Failed to create database for {$tenant->name}: " . $e->getMessage());
                continue;
            }

            // 2. Set the 'tenant' connection dynamically
            \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $dbName);
            \Illuminate\Support\Facades\DB::purge('tenant');
            \Illuminate\Support\Facades\DB::reconnect('tenant');

            // 3. Prepare artisan migration options
            $options = [
                '--database' => 'tenant',
                '--force'    => true, // skip confirmation in production
            ];

            if ($this->option('seed')) {
                $options['--seed'] = true;
            }

            $command = 'migrate';

            try {
                \Illuminate\Support\Facades\Artisan::call($command, $options);
                $this->line(\Illuminate\Support\Facades\Artisan::output());
                $this->info("✓ Successfully migrated: {$dbName}");
                
                // 4. Automatically seed if the database is missing crucial base identity
                if (\Illuminate\Support\Facades\DB::connection('tenant')->table('tenants')->count() === 0) {
                    \Illuminate\Support\Facades\Artisan::call('db:seed', [
                        '--database' => 'tenant',
                        '--force' => true
                    ]);
                    $this->info("✓ Automatically seeded Tenant identity & base data.\n");
                } else {
                    $this->info("\n"); // Just a newline for formatting
                }
            } catch (\Exception $e) {
                $this->error("Migration failed for {$dbName}: " . $e->getMessage() . "\n");
            }
        }

        $this->info("All active tenant databases have been migrated!");
    }
}
