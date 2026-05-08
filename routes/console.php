 <?php
use Illuminate\Support\Facades\Schedule;
 Schedule::command('wallet:release-pending')
    ->hourly()
    ->appendOutputTo(storage_path('logs/schedule.log'));

Schedule::command('wallet:auto-payout')
    ->dailyAt('02:00')
    ->appendOutputTo(storage_path('logs/schedule.log'));

Schedule::command('deliveries:daily-report')
    ->dailyAt('06:00')
    ->appendOutputTo(storage_path('logs/schedule.log'));

Schedule::command('sellers:remind-incomplete')
    ->dailyAt('10:00')
    ->appendOutputTo(storage_path('logs/schedule.log'));
    
Schedule::command('orders:purge-unpaid')
    ->dailyAt('02:00')          // Runs at 2 AM every day (quiet hours)
    ->withoutOverlapping()      // Prevents double-run if previous is still going
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/schedule.log'));
    
// Daily digest to admins at 8pm
Schedule::command('telegram:daily-digest')->dailyAt('20:00');

// Proactive checks every hour
Schedule::command('telegram:proactive-checks')->hourly();

// 

// // Release pending funds every hour
// Schedule::command('wallet:release-pending')->hourly();

// // Process auto payouts daily at 2 AM
// Schedule::command('wallet:auto-payout')->dailyAt('02:00');

// // Generate daily delivery report at 6 AM
// Schedule::command('deliveries:daily-report')->dailyAt('06:00');

// // Send reminders to sellers who started registration but never completed it
// Schedule::command('sellers:remind-incomplete')->dailyAt('10:00');