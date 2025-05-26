<?php

namespace App\Http\Middleware;

use App\Models\Content;
use App\Models\Menu;
use App\Models\ContentVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackContentVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track for authenticated users and successful responses
        if ($request->user() && 
            $response->getStatusCode() >= 200 && 
            $response->getStatusCode() < 300) {
            
            $this->trackContentVisit($request);
        }

        return $response;
    }

    /**
     * Track content/menu visit activity.
     */
    private function trackContentVisit(Request $request): void
    {
        $user = $request->user();
        $path = $request->path();
        $url = $request->fullUrl();
        
        // Skip API endpoints that shouldn't be tracked
        $skipPaths = [
            'api/user',
            'api/logout',
            'api/dashboard',
            'sanctum/csrf-cookie'
        ];
        
        foreach ($skipPaths as $skipPath) {
            if (str_contains($path, $skipPath)) {
                return;
            }
        }

        $contentId = null;
        $menuId = null;
        $pageType = 'general';
        $pageTitle = null;

        // Determine the type of content being accessed
        if (str_contains($path, 'api/contents/slug/')) {
            // Extract content slug from API route like /api/contents/slug/test-content
            $segments = explode('/', $path);
            $slugIndex = array_search('slug', $segments);
            
            if ($slugIndex !== false && isset($segments[$slugIndex + 1])) {
                $slug = $segments[$slugIndex + 1];
                $content = Content::where('slug', $slug)->first();
                
                if ($content) {
                    $contentId = $content->id;
                    $pageType = 'content';
                    $pageTitle = $content->title;
                }
            }
        } elseif (str_contains($path, 'api/contents/') && !str_contains($path, 'api/contents/slug/')) {
            // Extract content from direct API route like /api/contents/123 or /api/contents/test-content
            $segments = explode('/', $path);
            $contentsIndex = array_search('contents', $segments);
            
            if ($contentsIndex !== false && isset($segments[$contentsIndex + 1])) {
                $contentIdentifier = $segments[$contentsIndex + 1];
                
                // Try to find content by ID first, then by slug
                $content = null;
                if (is_numeric($contentIdentifier)) {
                    $content = Content::find($contentIdentifier);
                } else {
                    $content = Content::where('slug', $contentIdentifier)->first();
                }
                
                if ($content) {
                    $contentId = $content->id;
                    $pageType = 'content';
                    $pageTitle = $content->title;
                }
            }
        } elseif (str_contains($path, 'api/menus/')) {
            // Extract menu info from API route
            $segments = explode('/', $path);
            $menuIndex = array_search('menus', $segments);
            
            if ($menuIndex !== false && isset($segments[$menuIndex + 1])) {
                $menuId = (int) $segments[$menuIndex + 1];
                $menu = Menu::find($menuId);
                
                if ($menu) {
                    $pageType = 'menu';
                    $pageTitle = $menu->name;
                    
                    // If menu has associated content, track that too
                    if ($menu->content_id) {
                        $contentId = $menu->content_id;
                    }
                }
            }
        } elseif (str_contains($path, 'dashboard')) {
            $pageType = 'dashboard';
            $pageTitle = 'Dashboard';
        }

        // Create the visit record
        try {
            ContentVisit::create([
                'user_id' => $user->id,
                'content_id' => $contentId,
                'menu_id' => $menuId,
                'page_type' => $pageType,
                'page_title' => $pageTitle,
                'page_url' => $url,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'visited_at' => now(),
                'duration_seconds' => 0, // Will be updated by frontend tracking
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the request
            \Log::warning('Failed to track content visit: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'path' => $path,
                'url' => $url,
            ]);
        }
    }

    /**
     * Update visit duration for tracking time spent on page.
     */
    public static function updateVisitDuration(int $visitId, int $durationSeconds): bool
    {
        try {
            $visit = ContentVisit::find($visitId);
            if ($visit) {
                $visit->update(['duration_seconds' => $durationSeconds]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::warning('Failed to update visit duration: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get popular content based on visit counts.
     */
    public static function getPopularContent(int $limit = 10, int $days = 30): array
    {
        return ContentVisit::with('content:id,title,slug')
            ->whereNotNull('content_id')
            ->where('visited_at', '>=', now()->subDays($days))
            ->selectRaw('content_id, COUNT(*) as visit_count, MAX(visited_at) as last_visit')
            ->groupBy('content_id')
            ->orderBy('visit_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($visit) {
                return [
                    'content_id' => $visit->content_id,
                    'title' => $visit->content?->title,
                    'slug' => $visit->content?->slug,
                    'visit_count' => $visit->visit_count,
                    'last_visit' => $visit->last_visit,
                ];
            })
            ->toArray();
    }

    /**
     * Get popular menus based on visit counts.
     */
    public static function getPopularMenus(int $limit = 10, int $days = 30): array
    {
        return ContentVisit::with('menu:id,name')
            ->whereNotNull('menu_id')
            ->where('visited_at', '>=', now()->subDays($days))
            ->selectRaw('menu_id, COUNT(*) as visit_count, MAX(visited_at) as last_visit')
            ->groupBy('menu_id')
            ->orderBy('visit_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($visit) {
                return [
                    'menu_id' => $visit->menu_id,
                    'name' => $visit->menu?->name,
                    'visit_count' => $visit->visit_count,
                    'last_visit' => $visit->last_visit,
                ];
            })
            ->toArray();
    }
}
