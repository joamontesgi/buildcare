<?php

namespace App\Console\Commands;

use App\Services\CatalogImportService;
use Illuminate\Console\Command;

class ImportCatalogsCommand extends Command
{
    protected $signature = 'catalogs:import {--excel= : Path to excel directory}';

    protected $description = 'Import buildings/vendors/zones from JSON/Excel (catalog_items: use CatalogItemSeeder only)';

    public function handle(CatalogImportService $importService): int
    {
        $this->info('Importing BuildCare data from JSON/Excel (not catalog_items)...');
        $this->comment('Para catalog_items ejecuta: php artisan db:seed --class=CatalogItemSeeder');

        $counts = $importService->import($this->option('excel'));

        foreach ($counts as $key => $count) {
            $this->line(sprintf('  %-18s %d', ucfirst(str_replace('_', ' ', $key)).':', $count));
        }

        $this->info('Import completed.');

        return self::SUCCESS;
    }
}
