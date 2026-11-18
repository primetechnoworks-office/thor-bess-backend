<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();

        $thisMonth = Lead::where('created_at', '>=', $monthStart)->count();
        $lastMonth = Lead::whereBetween('created_at', [$lastMonthStart, $monthStart])->count();

        $stats = [
            'total' => Lead::count(),
            'new' => Lead::where('status', 'new')->count(),
            'month' => $thisMonth,
            'month_change' => $lastMonth ? round(($thisMonth - $lastMonth) / $lastMonth * 100) : null,
            'posts' => Post::published()->count(),
            'drafts' => Post::where('status', Post::STATUS_DRAFT)->count(),
        ];

        $byType = Lead::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');
        $byStatus = Lead::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        // Leads per day for the last 14 days (computed in PHP so it works on MySQL and SQLite alike).
        $from = now()->subDays(13)->startOfDay();
        $perDay = Lead::where('created_at', '>=', $from)->pluck('created_at')
            ->countBy(fn (Carbon $date) => $date->format('Y-m-d'));
        $trend = collect(range(0, 13))->map(function ($i) use ($from, $perDay) {
            $day = $from->copy()->addDays($i);

            return ['label' => $day->format('d M'), 'short' => $day->format('d'), 'total' => $perDay->get($day->format('Y-m-d'), 0)];
        });

        return view('admin.dashboard', [
            'stats' => $stats,
            'byType' => $byType,
            'byStatus' => $byStatus,
            'trend' => $trend,
            'trendMax' => max(1, $trend->max('total')),
            'recentLeads' => Lead::latest()->take(6)->get(),
            'recentPosts' => Post::latest('updated_at')->take(4)->get(),
        ]);
    }
}
