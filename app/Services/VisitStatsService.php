<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisitStatsService
{
    /**
     * Cache duration for dashboard stats in seconds (5 minutes).
     */
    public const CACHE_TTL_SECONDS = 300;

    /**
     * Generate an anonymized SHA-256 hash for the visitor.
     * Never stores raw IP or user agent.
     */
    public function makeVisitorHash(string $ip, string $userAgent, string $date): string
    {
        $appKey = (string) config('app.key', 'marketplace_fallback_salt');

        return hash('sha256', $ip.'|'.$userAgent.'|'.$date.'|'.$appKey);
    }

    /**
     * Detect if user agent belongs to a known bot or crawler.
     */
    public function isBot(string $userAgent): bool
    {
        if (trim($userAgent) === '') {
            return true;
        }

        $botPattern = '/bot|crawl|spider|slurp|google|bing|facebookexternalhit|whatsapp|telegram|curl|python|wget|postman/i';

        return (bool) preg_match($botPattern, $userAgent);
    }

    /**
     * Record a page visit atomically.
     */
    public function record(string $visitorHash, string $visitDate): void
    {
        try {
            $inserted = DB::table('visit')->insertOrIgnore([
                'visitor_hash' => $visitorHash,
                'visit_date' => $visitDate,
                'page_views' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if (! $inserted) {
                DB::table('visit')
                    ->where('visitor_hash', $visitorHash)
                    ->where('visit_date', $visitDate)
                    ->increment('page_views', 1, [
                        'updated_at' => Carbon::now(),
                    ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to record visitor stats: '.$e->getMessage());
        }
    }

    /**
     * Get compiled dashboard statistics cached for 5 minutes.
     *
     * @return array{
     *     todayVisitors: int,
     *     last7DaysVisitors: int,
     *     last30DaysVisitors: int,
     *     totalUniqueVisitorDays: int,
     *     todayPageViews: int,
     *     chartData: array<int, array{date: string, label: string, visitors: int, height_percent: int}>
     * }
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('admin_dashboard_visit_stats', self::CACHE_TTL_SECONDS, function (): array {
            $today = Carbon::today()->toDateString();
            $sevenDaysAgo = Carbon::today()->subDays(6)->toDateString();
            $thirtyDaysAgo = Carbon::today()->subDays(29)->toDateString();
            $fourteenDaysAgo = Carbon::today()->subDays(13)->toDateString();

            $todayVisitors = (int) DB::table('visit')
                ->where('visit_date', $today)
                ->count();

            $last7DaysVisitors = (int) DB::table('visit')
                ->where('visit_date', '>=', $sevenDaysAgo)
                ->count();

            $last30DaysVisitors = (int) DB::table('visit')
                ->where('visit_date', '>=', $thirtyDaysAgo)
                ->count();

            $totalUniqueVisitorDays = (int) DB::table('visit')->count();

            $todayPageViews = (int) DB::table('visit')
                ->where('visit_date', $today)
                ->sum('page_views');

            // 14-day chart data
            $raw14Days = DB::table('visit')
                ->whereBetween('visit_date', [$fourteenDaysAgo, $today])
                ->selectRaw('visit_date, count(*) as visitors')
                ->groupBy('visit_date')
                ->pluck('visitors', 'visit_date')
                ->all();

            $chartData = [];
            $maxVisitors = 0;

            for ($i = 13; $i >= 0; $i--) {
                $d = Carbon::today()->subDays($i);
                $dStr = $d->toDateString();
                $vCount = (int) ($raw14Days[$dStr] ?? 0);
                if ($vCount > $maxVisitors) {
                    $maxVisitors = $vCount;
                }

                $chartData[] = [
                    'date' => $dStr,
                    'label' => $d->format('m/d'),
                    'visitors' => $vCount,
                    'height_percent' => 0, // will compute after loop
                ];
            }

            $maxForScale = max(1, $maxVisitors);
            foreach ($chartData as &$dayItem) {
                $dayItem['height_percent'] = (int) round(($dayItem['visitors'] / $maxForScale) * 100);
            }
            unset($dayItem);

            return [
                'todayVisitors' => $todayVisitors,
                'last7DaysVisitors' => $last7DaysVisitors,
                'last30DaysVisitors' => $last30DaysVisitors,
                'totalUniqueVisitorDays' => $totalUniqueVisitorDays,
                'todayPageViews' => $todayPageViews,
                'chartData' => $chartData,
            ];
        });
    }

    /**
     * Delete visit rows older than the specified days threshold.
     */
    public function pruneOldVisits(int $days = 400): int
    {
        $cutoff = Carbon::today()->subDays($days)->toDateString();

        return DB::table('visit')->where('visit_date', '<', $cutoff)->delete();
    }
}

