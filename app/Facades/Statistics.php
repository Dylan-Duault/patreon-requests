<?php

namespace App\Facades;

use App\Services\StatisticsService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array getStatistics(\Carbon\CarbonInterface $startDate, \Carbon\CarbonInterface $endDate)
 * @method static string calculateGranularity(\Carbon\CarbonInterface $start, \Carbon\CarbonInterface $end)
 * @method static int|null getOldestPendingAge()
 *
 * @see \App\Services\StatisticsService
 */
class Statistics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StatisticsService::class;
    }
}
