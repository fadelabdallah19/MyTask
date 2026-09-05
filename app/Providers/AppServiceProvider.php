<?php

namespace App\Providers;

use App\Models\Notification as NotificationModel;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::listen(function ($query) {
            if (! isset($GLOBALS['_prof'])) {
                return;
            }

            $elapsed = microtime(true) - $GLOBALS['_prof'];

            error_log(sprintf(
                '[PROFILE] sql t=%5.3f qtime=%.1fms %s',
                $elapsed,
                $query->time,
                Str::limit(str_replace(["\n", "\t"], ' ', $query->sql), 160),
            ));
        });

        Paginator::useTailwind();

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('api-login', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        View::composer('layouts._nav', function ($view) {
            $count = 0;

            if ($user = auth()->user()) {
                $count = NotificationModel::forUser($user->id)->unread()->count();
            }

            $view->with('unreadCount', $count);
        });
    }
}
