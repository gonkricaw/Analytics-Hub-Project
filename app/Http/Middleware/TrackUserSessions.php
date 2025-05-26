<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSessions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track for authenticated API requests
        if ($request->user() && $request->is('api/*')) {
            $this->trackUserSession($request);
        }

        return $response;
    }

    /**
     * Track user session activity.
     */
    private function trackUserSession(Request $request): void
    {
        $user = $request->user();
        
        // Try to get session ID, fallback to a generated one for API requests
        try {
            $sessionId = $request->session()->getId();
        } catch (\Exception $e) {
            // For API requests without session store, generate a unique session ID
            $sessionId = 'api-session-' . $user->id . '-' . time();
        }
        
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Find or create the current session record
        $userSession = UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->first();

        if (!$userSession) {
            // Create new session record
            UserSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'login_at' => now(),
                'last_activity_at' => now(),
                'is_active' => true,
            ]);
        } else {
            // Update existing session activity
            $userSession->update([
                'last_activity_at' => now(),
                'ip_address' => $ipAddress, // Update in case of IP change
                'user_agent' => $userAgent, // Update in case of browser change
            ]);
        }

        // Update user's last active timestamp
        $user->updateLastActive();
    }

    /**
     * Mark session as inactive/logged out.
     */
    public static function markSessionLogout(string $sessionId, int $userId): void
    {
        UserSession::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->update([
                'logout_at' => now(),
                'is_active' => false,
            ]);
    }

    /**
     * Clean up old inactive sessions.
     */
    public static function cleanupOldSessions(): void
    {
        // Mark sessions as inactive if they haven't been active for more than 2 hours
        UserSession::where('is_active', true)
            ->where('last_activity_at', '<', now()->subHours(2))
            ->update([
                'logout_at' => now(),
                'is_active' => false,
            ]);
    }
}
