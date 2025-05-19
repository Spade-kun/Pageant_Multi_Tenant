<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Codedge\Updater\UpdaterManager;

class VersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'version:check {--update : Update to the latest version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update system version';

    /**
     * Execute the console command.
     */
    public function handle(UpdaterManager $updater)
    {
        $this->info('Checking for updates...');

        if ($updater->source()->isNewVersionAvailable()) {
            $currentVersion = $updater->source()->getVersionInstalled();
            $newVersion = $updater->source()->getVersionAvailable();

            $this->info("Current version: v{$currentVersion}");
            $this->info("New version available: v{$newVersion}");

            if ($this->option('update')) {
                $this->info('Updating to the latest version...');
                
                try {
                    $updater->source()->update();
                    $this->info('Update completed successfully!');
                } catch (\Exception $e) {
                    $this->error('Update failed: ' . $e->getMessage());
                }
            } else {
                $this->info('To update, run: php artisan version:check --update');
            }
        } else {
            $currentVersion = $updater->source()->getVersionInstalled();
            $this->info("You are running the latest version (v{$currentVersion})");
        }
    }
} 