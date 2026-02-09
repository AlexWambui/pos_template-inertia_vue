<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckShiftMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Skip for non-cashier roles
        if (!$user || !$user->isCashier()) {
            return $next($request);
        }
        
        // Allow access to shift-related routes
        $allowedRoutes = [
            'shifts.open',
            'shifts.store',
            'shifts.close',
            'shifts.update',
            'logout',
        ];
        
        if (in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }
        
        // Check if cashier has an open shift
        if (!$user->hasOpenShift()) {
            return redirect()->route('shifts.open');
        }
        
        return $next($request);
    }
}
