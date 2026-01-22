<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FreshSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fresh-seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Golește tabelele aplicației și rulează seeders (RoleSeeder și TenantSeeder)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('Ești sigur că vrei să golești toate tabelele aplicației?', true)) {
            $this->info('Operațiune anulată.');
            return Command::SUCCESS;
        }

        $this->info('Golesc tabelele aplicației...');

        // Dezactivează verificările de foreign key temporar
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Listează tabelele aplicației (exclude tabelele Laravel sistem)
        $tables = [
            'audit_logs',
            'children',
            'guardians',
            'play_session_intervals',
            'play_session_products',
            'play_sessions',
            'products',
            'scan_events',
            'tenants',
            'users',
            'roles',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    DB::table($table)->truncate();
                    $this->line("  ✓ Tabelul '{$table}' a fost golit");
                } catch (\Exception $e) {
                    $this->warn("  ⚠ Nu s-a putut goli tabelul '{$table}': " . $e->getMessage());
                }
            }
        }

        // Reactivează verificările de foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->info('Rulez seeders...');
        $this->newLine();

        // Rulează seeders
        $this->call('db:seed', [
            '--class' => 'DatabaseSeeder',
        ]);

        $this->newLine();
        $this->info('✅ Baza de date a fost resetată și populată cu succes!');

        return Command::SUCCESS;
    }
}
