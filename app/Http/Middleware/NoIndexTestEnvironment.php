<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoIndexTestEnvironment
{
    /**
     * Handle an incoming request.
     * Agrega headers X-Robots-Tag para bloquear indexación en test.corralx.com
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Si es el entorno de testing (test.corralx.com), bloquear indexación
        if (str_contains($request->getHost(), 'test.corralx.com')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet, noimageindex');
        }

        return $response;
    }
}

