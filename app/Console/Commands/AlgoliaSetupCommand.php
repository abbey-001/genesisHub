<?php

namespace App\Console\Commands;

use App\Services\AlgoliaSearchService;
use Illuminate\Console\Command;

/**
 * Artisan command: php artisan algolia:setup
 *
 * Full one-time setup sequence (v4 raw client — no scout-extended):
 *
 *  Step 1 — Configure index settings + replicas:
 *    php artisan algolia:setup --configure
 *
 *  Step 2 — Seed synonyms only (default behaviour):
 *    php artisan algolia:setup
 *
 *  Step 3 — Import your products:
 *    php artisan scout:import "App\Models\Product"
 *
 * Register in App\Console\Kernel (Laravel 9/10) or bootstrap/app.php (Laravel 11):
 *   $schedule->command('algolia:setup')->monthly();  // re-seed synonyms if needed
 */
class AlgoliaSetupCommand extends Command
{
    protected $signature   = 'algolia:setup {--configure : Also push index settings and replica config}';
    protected $description = 'Seed Algolia synonyms (and optionally configure index settings)';

    public function handle(AlgoliaSearchService $algolia): int
    {
        if ($this->option('configure')) {
            $this->info('Configuring index settings and replicas...');
            $algolia->configureIndex();
            $this->newLine();
        }

        $this->info('Seeding default ecommerce synonyms...');
        $algolia->seedDefaultSynonyms();

        $this->newLine();
        $this->info('✓ Done. Synonyms are live immediately — no reindex needed.');
        $this->newLine();
        $this->line('Next steps if you haven\'t already:');
        $this->line('  php artisan algolia:setup --configure   # push index settings + replicas');
        $this->line('  php artisan scout:import "App\Models\Product"  # index your products');

        return self::SUCCESS;
    }
}