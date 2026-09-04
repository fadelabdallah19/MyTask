<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;

class AdminLogController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        $logs = ActivityLog::with('user:id,name,email')
            ->latest()
            ->paginate(20);

        return view('admin.logs.index', compact('logs'));
    }
}
