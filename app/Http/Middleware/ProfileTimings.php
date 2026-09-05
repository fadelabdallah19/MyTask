<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileTimings
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $GLOBALS['_prof'] = $start;

        error_log(sprintf('[PROFILE] start %s', $request->getPathInfo()));

        $response = $next($request);

        error_log(sprintf('[PROFILE] end %s t=%.3f', $request->getPathInfo(), microtime(true) - $start));

        return $response;
    }
}
