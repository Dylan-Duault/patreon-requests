<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Statistics;
use App\Http\Controllers\Controller;
use App\Models\VideoRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StatisticsController extends Controller
{
    public function index(Request $request): Response
    {
        $isAllTime = $request->query('start_date') === 'all';

        $startDate = match ($request->query('start_date')) {
            null => Carbon::now()->subDays(30)->startOfDay(),
            'all' => VideoRequest::min('requested_at')
                ? Carbon::parse(VideoRequest::min('requested_at'))->startOfDay()
                : Carbon::now()->subDays(30)->startOfDay(),
            default => Carbon::parse($request->query('start_date'))->startOfDay(),
        };

        $endDate = $request->query('end_date')
            ? Carbon::parse($request->query('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        return Inertia::render('admin/Statistics', [
            ...Statistics::getStatistics($startDate, $endDate),
            'isAllTime' => $isAllTime,
        ]);
    }
}
