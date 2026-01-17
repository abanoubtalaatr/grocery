<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/setup-intent',
        'api/cards',
        'api/charge-card',
        'api/cards/*',
        // Public auth routes - no authentication required
        'api/auth/register',
        'api/auth/login',
        'api/auth/forgot-password',
        'api/auth/verify-otp',
        'api/auth/reset-password',
        'api/auth/google',
        // Public contact form
        'api/contact',
    ];
}
