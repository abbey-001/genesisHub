<?php

namespace App\Console\Commands;

use App\Services\SellerWalletService;
use Illuminate\Console\Command;

class ReleasePendingFunds extends Command
{
    protected $signature = 'wallet:release-pending';

    protected $description = 'Release seller pending earnings whose hold period has expired';

    public function handle(SellerWalletService $walletService): int
    {
        $this->info('Checking for pending funds to release...');

        try {
            $result = $walletService->releasePendingFunds();

            $this->line('');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total transactions checked',    $result['total_checked']],
                    ['Released to available balance', $result['released_count']],
                    ['Skipped (hold not expired)',    $result['skipped_count']],
                    ['Errors',                        count($result['errors'])],
                ]
            );

            if (!empty($result['errors'])) {
                $this->warn('⚠️  Some transactions could not be released — check logs.');
                foreach ($result['errors'] as $err) {
                    $this->error("  TX #{$err['transaction_id']}: {$err['error']}");
                }
                return 1;
            }

            if ($result['released_count'] > 0) {
                $this->info("✅ {$result['released_count']} transaction(s) released successfully.");
            } else {
                $this->comment('📋 No transactions were due for release.');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Fatal error: ' . $e->getMessage());
            return 1;
        }
    }
}