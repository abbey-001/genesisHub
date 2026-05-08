<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlgoliaSearchService;

class AlgoliaConfigure extends Command
{
    protected $signature   = 'algolia:configure';
    protected $description = 'Push index settings and replicas to Algolia';

    public function handle(AlgoliaSearchService $service): void
    {
        $this->info('Configuring Algolia index...');
        $service->configureIndex();
        $this->info('Done.');
    }
}