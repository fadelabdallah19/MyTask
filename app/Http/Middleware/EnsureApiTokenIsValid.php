<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenIsValid
{
    /**
     * Authenticate the request using a Bearer API token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        $user = $token
            ? User::where('api_token', $token)->first()
            : null;

        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        Auth::setUser($user);

        return $next($request);
    }
}
