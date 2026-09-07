<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateIngestToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.ingest.token');
        $provided = $request->bearerToken();

        if (! is_string($expected) || $expected === '' || ! is_string($provided) || ! hash_equals($expected, $provided)) {
            abort(401, 'Invalid ingest token.');
        }

        return $next($request);
    }
}
