<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // echo "Redirecting to login page...";
        // exit; // Debugging line to check if this method is being called
        // return $request->expectsJson() ? null : route('login');
        if (!$request->expectsJson()) {
            return route('login');
        }
    }
}
