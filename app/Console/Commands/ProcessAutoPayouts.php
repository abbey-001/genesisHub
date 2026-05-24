<?php

namespace App\Console\Commands;

use App\Models\Payout;
use App\Models\SellerPayoutSettings;
use App\Services\SellerWalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAutoPayouts extends Command
{
    protected $signature = 'wallet:auto-payout
                            {--dry-run : Preview what would happen without creating payouts}
                            {--seller= : Restrict to a single seller ID}';

    protected $description = 'Process automatic payouts for sellers who have enabled auto-payout';

    protected array $stats = [
        'total_checked'    => 0,
        'processed'        => 0,
        'skipped'          => 0,
        'failed'           => 0,
        'amount_processed' => 0,
    ];

    public function handle(SellerWalletService $walletService): int
    {
        $startTime      = now();
        $isDryRun       = $this->option('dry-run');
        $specificSeller = $this->option('seller');

        $this->info('========================================');
        $this->info('  AUTO-PAYOUT PROCESSING STARTED');
        $this->info('========================================');
        $this->info('Time: ' . $startTime->format('Y-m-d H:i:s'));

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE — no payouts will be created');
        }
        if ($specificSeller) {
            $this->info("📌 Restricted to Seller ID: {$specificSeller}");
        }

        $this->line('');

        try {
            $query = SellerPayoutSettings::where('auto_payout_enabled', true)
                ->whereNotNull('auto_payout_threshold')
                ->where('auto_payout_threshold', '>', 0)
                ->with(['seller.wallet', 'seller.user', 'seller.payoutSettings']);

            if ($specificSeller) {
                $query->where('seller_id', $specificSeller);
            }

            $settings = $query->get();
            $this->stats['total_checked'] = $settings->count();

            if ($this->stats['total_checked'] === 0) {
                $this->warn('⚠️  No sellers found with auto-payout enabled.');
                return 0;
            }

            $this->info("Found {$this->stats['total_checked']} seller(s).");
            $this->line('');

            $bar = $this->output->createProgressBar($this->stats['total_checked']);
            $bar->start();

            foreach ($settings as $setting) {
                $this->processSeller($setting, $walletService, $isDryRun);
                $bar->advance();
            }

            $bar->finish();
            $this->line("\n");

            $this->displayResults($startTime);
            $this->logExecution($startTime, $isDryRun);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Fatal error: ' . $e->getMessage());
            Log::error('wallet:auto-payout command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stats' => $this->stats,
            ]);
            return 1;
        }
    }

    protected function processSeller(
        SellerPayoutSettings $setting,
        SellerWalletService  $walletService,
        bool                 $isDryRun
    ): void {
        $seller   = $setting->seller;
        $sellerId = $seller->id;

        try {
            $validation = $this->validateSeller($seller, $setting, $walletService);

            if (!$validation['valid']) {
                $this->stats['skipped']++;
                $this->logSkip($sellerId, $validation['reason']);
                return;
            }

            $wallet       = $validation['wallet'];
            $payoutAmount = $wallet->balance;

            if ($isDryRun) {
                $this->stats['processed']++;
                $this->stats['amount_processed'] += $payoutAmount;
                $this->line("\n[DRY RUN] Would payout Seller #{$sellerId}: ₦" . number_format($payoutAmount, 2));
                return;
            }

            $payout = $walletService->requestPayout($seller, [
                'amount'        => $payoutAmount,
                'payout_method' => $setting->preferred_method,
                'notes'         => 'Auto-payout: threshold ₦' . number_format($setting->auto_payout_threshold, 2),
            ]);

            $this->stats['processed']++;
            $this->stats['amount_processed'] += $payoutAmount;
            $this->logSuccess($sellerId, $payout);

        } catch (\Exception $e) {
            $this->stats['failed']++;
            $this->logFailure($sellerId, $e);
        }
    }

    protected function validateSeller(
        \App\Models\Seller   $seller,
        SellerPayoutSettings $setting,
        SellerWalletService  $walletService
    ): array {
        if (!$seller->is_verified) {
            return ['valid' => false, 'reason' => "Not verified (status={$seller->verification_status})"];
        }

        if (!$seller->user) {
            return ['valid' => false, 'reason' => 'User account missing'];
        }

        $wallet = $walletService->getWallet($seller);

        if ($wallet->balance < $setting->auto_payout_threshold) {
            return [
                'valid'  => false,
                'reason' => 'Balance ₦' . number_format($wallet->balance, 2) .
                            ' below threshold ₦' . number_format($setting->auto_payout_threshold, 2),
            ];
        }

        $minimumPayout = (float) ($setting->minimum_payout ?? 10.00);
        if ($wallet->balance < $minimumPayout) {
            return [
                'valid'  => false,
                'reason' => 'Balance below minimum payout ₦' . number_format($minimumPayout, 2),
            ];
        }

        $hasPendingPayout = Payout::where('seller_id', $seller->id)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($hasPendingPayout) {
            return ['valid' => false, 'reason' => 'Existing payout already in progress'];
        }

        $recentAutoPayout = Payout::where('seller_id', $seller->id)
            ->where('notes', 'like', '%Auto-payout%')
            ->where('requested_at', '>=', now()->subHours(24))
            ->exists();

        if ($recentAutoPayout) {
            return ['valid' => false, 'reason' => 'Auto-payout already created in last 24 hours'];
        }

        return ['valid' => true, 'wallet' => $wallet];
    }

    protected function displayResults($startTime): void
    {
        $endTime  = now();
        $duration = $endTime->diffInSeconds($startTime);

        $this->line('');
        $this->info('========================================');
        $this->info('  EXECUTION SUMMARY');
        $this->info('========================================');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Checked',    $this->stats['total_checked']],
                ['Processed',        $this->stats['processed']],
                ['Skipped',          $this->stats['skipped']],
                ['Failed',           $this->stats['failed']],
                ['Amount Processed', '₦' . number_format($this->stats['amount_processed'], 2)],
                ['Duration',         $duration . 's'],
                ['Completed At',     $endTime->format('Y-m-d H:i:s')],
            ]
        );

        if ($this->stats['failed'] > 0) {
            $this->warn("\n⚠️  Some payouts failed — check logs.");
        } elseif ($this->stats['processed'] > 0) {
            $this->info("\n✅ All payouts processed successfully.");
        } else {
            $this->comment("\n📋 No payouts were processed.");
        }

        $this->line('');
    }

    protected function logSuccess(int $sellerId, Payout $payout): void
    {
        Log::info('Auto-payout created', [
            'seller_id' => $sellerId,
            'payout_id' => $payout->id,
            'amount'    => $payout->amount,
            'method'    => $payout->payout_method,
        ]);
    }

    protected function logSkip(int $sellerId, string $reason): void
    {
        Log::debug('Auto-payout skipped', ['seller_id' => $sellerId, 'reason' => $reason]);
    }

    protected function logFailure(int $sellerId, \Exception $e): void
    {
        $this->error("\n❌ Auto-payout failed for Seller #{$sellerId}: " . $e->getMessage());
        Log::error('Auto-payout failed', [
            'seller_id' => $sellerId,
            'error'     => $e->getMessage(),
            'trace'     => $e->getTraceAsString(),
        ]);
    }

    protected function logExecution($startTime, bool $isDryRun): void
    {
        Log::info('wallet:auto-payout completed', [
            'dry_run'          => $isDryRun,
            'stats'            => $this->stats,
            'started_at'       => $startTime->toDateTimeString(),
            'completed_at'     => now()->toDateTimeString(),
            'duration_seconds' => now()->diffInSeconds($startTime),
        ]);
    }
}
