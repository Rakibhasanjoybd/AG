<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RegistrationStep
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // If reg_step is not set or is 0, auto-complete it
        // This allows users to access dashboard directly after registration
        if (!$user->reg_step) {
            // Auto-complete registration step
            $user->reg_step = 1;
            $user->save();
        }
        
        return $next($request);
    }
}
