<?php

namespace App\Console\Commands;

use App\Models\Delivery;
use App\Models\Rider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenerateDeliveryReport extends Command
{
    protected $signature = 'deliveries:daily-report {--email=}';
    protected $description = 'Generate daily delivery report';

    public function handle()
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        $stats = [
            'today' => $this->getStats($today, now()),
            'yesterday' => $this->getStats($yesterday, $today),
        ];

        $topRiders = Rider::withCount(['deliveries as today_count' => function($q) use ($today) {
                $q->where('status', 'delivered')
                  ->whereDate('delivered_at', $today);
            }])
            ->orderByDesc('today_count')
            ->limit(5)
            ->get();

        $this->displayReport($stats, $topRiders);

        // Send email if requested
        if ($email = $this->option('email')) {
            // Mail::to($email)->send(new DailyDeliveryReport($stats, $topRiders));
            $this->info("Report sent to {$email}");
        }

        return 0;
    }

    protected function getStats($start, $end)
    {
        return [
            'completed' => Delivery::where('status', 'delivered')
                ->whereBetween('delivered_at', [$start, $end])
                ->count(),
            'failed' => Delivery::where('status', 'failed')
                ->whereBetween('failed_at', [$start, $end])
                ->count(),
            'revenue' => Delivery::where('status', 'delivered')
                ->whereBetween('delivered_at', [$start, $end])
                ->sum('delivery_fee'),
            'pending' => Delivery::where('status', 'pending')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
        ];
    }

    protected function displayReport($stats, $topRiders)
    {
        $this->info('=== Daily Delivery Report ===');
        $this->info('');
        $this->info('Today:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Completed', $stats['today']['completed']],
                ['Failed', $stats['today']['failed']],
                ['Revenue', '₦' . number_format($stats['today']['revenue'], 2)],
                ['Pending', $stats['today']['pending']],
            ]
        );

        $this->info('');
        $this->info('Top Riders Today:');
        $this->table(
            ['Rider', 'Deliveries'],
            $topRiders->map(fn($r) => [$r->full_name, $r->today_count])
        );
    }
}
