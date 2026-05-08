<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    /**
     * Called by an external cron service (e.g. cron-job.org) every minute.
     * Fires the Laravel scheduler exactly as the crontab entry would.
     *
     * Security: protected by a secret token in .env (CRON_SECRET).
     * The external service must pass it as a Bearer token or query param.
     */
    public function run(Request $request)
    {
        // ── Authenticate ──────────────────────────────────────────────
        $secret = config('app.cron_secret');

        if (empty($secret)) {
            Log::error('[CronHTTP] CRON_SECRET is not set in .env — refusing to run.');
            return response()->json(['error' => 'Cron not configured.'], 500);
        }

        $provided = $request->bearerToken()          // Authorization: Bearer <token>
            ?? $request->query('token')              // ?token=<token>
            ?? $request->header('X-Cron-Token');     // X-Cron-Token: <token>

        if (!hash_equals($secret, (string) $provided)) {
            Log::warning('[CronHTTP] Unauthorized cron attempt.', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        // ── Run the scheduler ─────────────────────────────────────────
        // This is identical to what the crontab entry does:
        //   php artisan schedule:run
        // Commands that are not due yet are skipped automatically.
        $start = microtime(true);

        try {
            Artisan::call('schedule:run');
            $output = Artisan::output();
        } catch (\Throwable $e) {
            Log::error('[CronHTTP] schedule:run threw an exception.', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Scheduler failed.'], 500);
        }

        $elapsed = round((microtime(true) - $start) * 1000);

        Log::info('[CronHTTP] schedule:run completed.', [
            'elapsed_ms' => $elapsed,
            'output'     => trim($output) ?: 'No commands due.',
        ]);

        // Return a minimal response — cron-job.org has a response size limit.
        // Full output is in the Laravel log; we only send a short status here.
        return response()->json([
            'ok'         => true,
            'elapsed_ms' => $elapsed,
            // Truncate to 200 chars max — enough to show which commands ran
            // without blowing past cron-job.org's "output too large" threshold.
            'output'     => mb_substr(trim($output) ?: 'No commands due.', 0, 200),
        ]);
    }
}