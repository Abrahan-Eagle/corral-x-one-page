<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * 
     * For API-only applications, always return null to prevent route exceptions.
     */
    protected function redirectTo(Request $request): ?string
    {
        // API-only: siempre retornar null para evitar error "Route [login] not defined"
        return null;
    }
}
