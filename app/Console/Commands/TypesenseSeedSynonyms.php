<?php
// ============================================================
// File: app/Console/Commands/TypesenseSeedSynonyms.php
// ============================================================
namespace App\Console\Commands;

use App\Services\TypesenseSearchService;
use Illuminate\Console\Command;

class TypesenseSeedSynonyms extends Command
{
    protected $signature   = 'typesense:seed-synonyms';
    protected $description = 'Seed default product synonyms into Typesense (sofa=couch, phone=mobile, etc.)';

    public function handle(TypesenseSearchService $ts): int
    {
        $collection = (new \App\Models\Product)->searchableAs();
        $this->info("Seeding synonyms into Typesense collection: [{$collection}]");

        try {
            $ts->seedDefaultSynonyms();
            $this->info('✓ All synonyms seeded successfully.');
            $this->line('  Add more in TypesenseSearchService::seedDefaultSynonyms()');
        } catch (\Typesense\Exceptions\ObjectNotFound $e) {
            $this->error("Collection [{$collection}] not found in Typesense.");
            $this->line('  Make sure you ran: php artisan scout:index products');
            $this->line('  And that TYPESENSE_API_KEY / TYPESENSE_HOST in .env are correct.');
            return 1;
        } catch (\Exception $e) {
            $this->error('Failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}