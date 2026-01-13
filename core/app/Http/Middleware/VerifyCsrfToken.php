<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * Note: Only IPN/webhook endpoints should be excluded, never user-facing financial endpoints
     *
     * @var array<int, string>
     */
    protected $except = [
        'ipn*',
        'webhook/*'
    ];
}
