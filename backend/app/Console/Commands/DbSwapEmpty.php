<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DbSwapEmpty extends Command
{
    protected $signature = 'db:swap {--restore : Restore the original database}';

    protected $description = 'Toggle between real database and empty state (users only) for UI testing';

    public function handle(): int
    {
        $dbPath = database_path('database.sqlite');
        $backupPath = database_path('database.sqlite.bak');

        if ($this->option('restore')) {
            return $this->restore($dbPath, $backupPath);
        }

        return $this->swapToEmpty($dbPath, $backupPath);
    }

    private function swapToEmpty(string $dbPath, string $backupPath): int
    {
        if (file_exists($backupPath)) {
            $this->error('Already in empty state. Run with --restore first.');

            return self::FAILURE;
        }

        if (! file_exists($dbPath)) {
            $this->error('No database found at '.$dbPath);

            return self::FAILURE;
        }

        // Back up the real database
        copy($dbPath, $backupPath);
        unlink($dbPath);
        touch($dbPath);

        $this->info('Original database backed up.');

        // Migrate fresh + seed only users
        Artisan::call('migrate', ['--force' => true], $this->output);
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\EmptyStateSeeder',
            '--force' => true,
        ], $this->output);

        $this->newLine();
        $this->info('Swapped to empty state. Only users seeded.');
        $this->comment('Run `php artisan db:swap --restore` to get your data back.');

        return self::SUCCESS;
    }

    private function restore(string $dbPath, string $backupPath): int
    {
        if (! file_exists($backupPath)) {
            $this->error('No backup found. You\'re already on the real database.');

            return self::FAILURE;
        }

        unlink($dbPath);
        rename($backupPath, $dbPath);

        $this->info('Original database restored.');

        return self::SUCCESS;
    }
}
