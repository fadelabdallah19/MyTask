<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        [$byStatus, $byPriority] = Cache::remember("report:{$user->id}", now()->addSeconds(60), function () use ($user): array {
            $byStatus = $user->tasks()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();

            $byPriority = $user->tasks()
                ->selectRaw('priority, count(*) as total')
                ->groupBy('priority')
                ->pluck('total', 'priority')
                ->all();

            return [$byStatus, $byPriority];
        });

        $totalTasks = array_sum($byStatus);

        return view('report.index', compact('byStatus', 'byPriority', 'totalTasks'));
    }
}
