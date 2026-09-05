<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class VercelCronController extends Controller
{
    /**
     * Trigger the deadline reminder scheduler from the Vercel Cron endpoint.
     */
    public function __invoke(Request $request): Response
    {
        $secret = env('CRON_SECRET');

        if (! $secret || $request->bearerToken() !== $secret) {
            abort(401);
        }

        Artisan::call('tasks:send-reminders');

        return response(Artisan::output())->header('Content-Type', 'text/plain');
    }
}
