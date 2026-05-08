<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\IncompleteSellerRegistration;
use Illuminate\Console\Command;

/**
 * Finds seller Users who have no Seller record (social login incomplete registrations)
 * and sends them a reminder email if they registered 24–48 hours ago.
 *
 * Schedule in app/Console/Kernel.php:
 *
 *   $schedule->command('sellers:remind-incomplete')->dailyAt('10:00');
 *
 * Or in routes/console.php (Laravel 10+):
 *
 *   Schedule::command('sellers:remind-incomplete')->dailyAt('10:00');
 */
class SendIncompleteSellerReminders extends Command
{
    protected $signature   = 'sellers:remind-incomplete';
    protected $description = 'Send reminder emails to sellers who started social registration but never completed their shop setup';

    public function handle(): int
    {
        // Find users who:
        //  1. Are type 'seller'
        //  2. Registered via social (no password set — completed via social flow)
        //  3. Have NO Seller record (never finished the completion form)
        //  4. Registered between 24–72 hours ago (sweet spot: past the "just did it" window)
        //  5. Have not been soft-deleted
        $incomplete = User::where('user_type', 'seller')
            ->whereNull('password')                            // social-only
            ->whereDoesntHave('seller')                        // no shop/seller record
            ->whereBetween('created_at', [
                now()->subHours(72),
                now()->subHours(24),
            ])
            ->whereNull('deleted_at')
            ->get();

        $count = 0;

        foreach ($incomplete as $user) {
            try {
                $user->notify(new IncompleteSellerRegistration());
                $count++;
                $this->line("  ✓ Reminded: {$user->email}");
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed for {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Sent {$count} reminder(s).");

        return self::SUCCESS;
    }
}