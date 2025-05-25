<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if ($request->user()) {
            $user = $request->user();
            
            // Check if user has been inactive for too long (30 minutes default)
            $inactivityLimit = config('auth.inactivity_limit', 30); // minutes
            
            if ($user->isInactive($inactivityLimit)) {
                // Logout user by deleting current token (if it exists)
                $currentToken = $user->currentAccessToken();
                if ($currentToken) {
                    $currentToken->delete();
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired due to inactivity. Please login again.',
                    'error_code' => 'SESSION_EXPIRED'
                ], 401);
            }
            
            // Update last active timestamp
            $user->updateLastActive();
        }
        
        return $next($request);
    }
}
