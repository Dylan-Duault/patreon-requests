<?php

namespace App\Services;

use App\Models\VideoRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    private const CACHE_TTL = 60; // 1 minute

    public function getStatistics(Carbon $startDate, Carbon $endDate): array
    {

        $granularity = $this->calculateGranularity($startDate, $endDate);
        $cacheKey = $this->getCacheKey($startDate, $endDate, $granularity);

        $chartData = Cache::remember($cacheKey, self::CACHE_TTL, fn () => [
            'requestsOverTime' => $this->getRequestsOverTime($startDate, $endDate, $granularity),
            'avgDurationOverTime' => $this->getAvgDurationOverTime($startDate, $endDate, $granularity),
            'avgCompletionTimeOverTime' => $this->getAvgCompletionTimeOverTime($startDate, $endDate, $granularity),
            'memberLeaderboard' => $this->getMemberLeaderboard($startDate, $endDate),
        ]);

        return [
            ...$chartData,
            'oldestPendingDays' => $this->getOldestPendingAge(),
            'granularity' => $granularity,
            'dateRange' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }

    public function calculateGranularity(Carbon $start, Carbon $end): string
    {
        $days = $start->diffInDays($end);

        if ($days < 14) {
            return 'daily';
        }

        if ($days < 90) {
            return 'weekly';
        }

        return 'monthly';
    }

    public function getOldestPendingAge(): ?int
    {
        $oldest = VideoRequest::pending()
            ->orderBy('requested_at', 'asc')
            ->first();

        if (! $oldest) {
            return null;
        }

        return (int) $oldest->requested_at->diffInDays(now());
    }

    public function getMemberLeaderboard(Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        $leaderboard = DB::table('video_requests')
            ->join('users', 'video_requests.user_id', '=', 'users.id')
            ->whereBetween('video_requests.requested_at', [$startDate, $endDate])
            ->select(
                'users.id',
                'users.name',
                'users.avatar',
                DB::raw('COUNT(*) as request_count'),
                DB::raw("SUM(CASE WHEN video_requests.rating = 'up' THEN 1 ELSE 0 END) as up_count"),
                DB::raw("SUM(CASE WHEN video_requests.rating IS NOT NULL THEN 1 ELSE 0 END) as rated_count")
            )
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->orderByDesc('request_count')
            ->limit($limit)
            ->get();

        return $leaderboard->map(fn ($item) => [
            'user' => [
                'id' => $item->id,
                'name' => $item->name,
                'avatar' => $item->avatar,
            ],
            'request_count' => (int) $item->request_count,
            'up_percentage' => $item->rated_count > 0
                ? round($item->up_count * 100.0 / $item->rated_count, 1)
                : null,
        ])->toArray();
    }

    private function getCacheKey(Carbon $start, Carbon $end, string $granularity): string
    {
        return sprintf(
            'admin:stats:%s:%s:%s',
            $start->toDateString(),
            $end->toDateString(),
            $granularity
        );
    }

    private function getDateFormat(string $granularity): string
    {
        return match ($granularity) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%x-%v',
            'monthly' => '%Y-%m',
        };
    }

    private function getRequestsOverTime(Carbon $start, Carbon $end, string $granularity): array
    {
        $dateFormat = $this->getDateFormat($granularity);
        
        $results = VideoRequest::query()
            ->whereBetween('requested_at', [$start, $end])
            ->select(
                DB::raw("DATE_FORMAT(requested_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();


        $this->fillMissingPeriods(
            $results->pluck('count', 'period')->toArray(),
            $start,
            $end,
            $granularity
        );

        return $this->fillMissingPeriods(
            $results->pluck('count', 'period')->toArray(),
            $start,
            $end,
            $granularity
        );
    }

    private function getAvgDurationOverTime(Carbon $start, Carbon $end, string $granularity): array
    {
        $dateFormat = $this->getDateFormat($granularity);

        $results = VideoRequest::query()
            ->whereBetween('requested_at', [$start, $end])
            ->whereNotNull('duration_seconds')
            ->select(
                DB::raw("DATE_FORMAT(requested_at, '{$dateFormat}') as period"),
                DB::raw('AVG(duration_seconds) as avg_duration')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $this->fillMissingPeriods(
            $results->pluck('avg_duration', 'period')->map(fn ($v) => round($v))->toArray(),
            $start,
            $end,
            $granularity,
            null
        );
    }

    private function getAvgCompletionTimeOverTime(Carbon $start, Carbon $end, string $granularity): array
    {
        $dateFormat = $this->getDateFormat($granularity);

        $results = VideoRequest::query()
            ->completed()
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('completed_at')
            ->select(
                DB::raw("DATE_FORMAT(completed_at, '{$dateFormat}') as period"),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, requested_at, completed_at)) as avg_hours')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $this->fillMissingPeriods(
            $results->pluck('avg_hours', 'period')->map(fn ($v) => round($v, 1))->toArray(),
            $start,
            $end,
            $granularity,
            null
        );
    }

    private function fillMissingPeriods(
        array $data,
        Carbon $start,
        Carbon $end,
        string $granularity,
        mixed $defaultValue = 0
    ): array {
        $periods = $this->generatePeriods($start, $end, $granularity);
        $filled = [];

        foreach ($periods as $period => $label) {
            $filled[] = [
                'period' => $period,
                'label' => $label,
                'value' => $data[$period] ?? $defaultValue,
            ];
        }

        return $filled;
    }

    private function generatePeriods(Carbon $start, Carbon $end, string $granularity): array
    {
        $periods = [];
        $current = $start->copy();

        while ($current <= $end) {
            switch ($granularity) {
                case 'daily':
                    $key = $current->format('Y-m-d');
                    $label = $current->format('M j');
                    $current->addDay();
                    break;
                case 'weekly':
                    $key = $current->format('o-W');
                    $label = 'W'.$current->format('W');
                    $current->addWeek();
                    break;
                case 'monthly':
                    $key = $current->format('Y-m');
                    $label = $current->format('M Y');
                    $current->addMonth();
                    break;
            }
            $periods[$key] = $label;
        }

        return $periods;
    }
}
