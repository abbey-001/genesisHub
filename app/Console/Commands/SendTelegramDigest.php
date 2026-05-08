<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Services\Telegram\AdminTelegramService;
use App\Services\Telegram\SellerTelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command that fires all proactive Telegram checks.
 *
 * Schedule in app/Console/Kernel.php (Laravel 10) or bootstrap/app.php (L11):
 *
 *   // Daily digest to admins at 8pm
 *   $schedule->command('telegram:daily-digest')->dailyAt('20:00');
 *
 *   // Proactive checks every hour
 *   $schedule->command('telegram:proactive-checks')->hourly();
 *
 * Or if using the existing CronController-based schedule (as seen in web.php),
 * call these commands from within your cron route or scheduler.
 */
class SendTelegramDigest extends Command
{
    protected $signature   = 'telegram:daily-digest';
    protected $description = 'Send the daily summary digest to all registered admins via Telegram.';

    public function __construct(
        protected AdminTelegramService $adminTelegram,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Sending daily admin digest...');

        try {
            $this->adminTelegram->sendDailyDigest();
            $this->adminTelegram->sendPayoutDailySummary();
            $this->info('✅ Daily digest sent.');
        } catch (\Exception $e) {
            Log::error('telegram:daily-digest failed', ['error' => $e->getMessage()]);
            $this->error('❌ ' . $e->getMessage());
        }
    }
}