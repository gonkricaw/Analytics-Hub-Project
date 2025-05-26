<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Models\ContentVisit;
use App\Models\SystemConfiguration;
use App\Models\Notification;
use App\Models\Content;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard data for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = [
                'jumbotron' => $this->getJumbotronData(),
                'loginStats' => $this->getLoginStats(),
                'notifications' => $this->getRecentNotifications(),
                'onlineUsers' => $this->getOnlineUsers(),
                'frequentUsers' => $this->getFrequentUsers(),
                'frequentContent' => $this->getFrequentContent(),
                'marqueeText' => $this->getMarqueeText(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get jumbotron carousel data.
     */
    public function getJumbotronData(): array
    {
        $jumbotronConfig = SystemConfiguration::getByKey('dashboard_jumbotron', []);
        
        // Default jumbotron data if not configured
        if (empty($jumbotronConfig)) {
            $jumbotronConfig = [
                'slides' => [
                    [
                        'id' => 1,
                        'title' => 'Welcome to Indonet Analytics Hub',
                        'subtitle' => 'Your comprehensive analytics dashboard',
                        'image' => '/images/hero/hero-bg.jpg',
                        'button_text' => 'Get Started',
                        'button_link' => '#',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Powerful Analytics',
                        'subtitle' => 'Track and analyze your data with ease',
                        'image' => '/images/hero/hero-bg-2.jpg',
                        'button_text' => 'Learn More',
                        'button_link' => '#',
                    ],
                ],
                'settings' => [
                    'autoplay' => true,
                    'interval' => 5000,
                    'indicators' => true,
                    'controls' => true,
                ],
            ];
        }

        return $jumbotronConfig;
    }

    /**
     * Get login statistics for the last 15 days.
     */
    public function getLoginStats(): array
    {
        $stats = UserSession::select(
                DB::raw('DATE(login_at) as date'),
                DB::raw('COUNT(*) as logins'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->where('login_at', '>=', now()->subDays(15))
            ->groupBy(DB::raw('DATE(login_at)'))
            ->orderBy('date')
            ->get();

        // Fill missing dates with zero values
        $dateRange = collect();
        for ($i = 14; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dateRange->push($date);
        }

        $chartData = $dateRange->map(function ($date) use ($stats) {
            $stat = $stats->firstWhere('date', $date);
            return [
                'date' => Carbon::parse($date)->format('M j'),
                'logins' => $stat ? $stat->logins : 0,
                'unique_users' => $stat ? $stat->unique_users : 0,
            ];
        });

        return [
            'chartData' => $chartData->values()->toArray(),
            'totalLogins' => $stats->sum('logins'),
            'totalUniqueUsers' => UserSession::where('login_at', '>=', now()->subDays(15))
                ->distinct('user_id')->count(),
        ];
    }

    /**
     * Get recent notifications for the dashboard widget.
     */
    public function getRecentNotifications(): array
    {
        $notifications = Notification::select('id', 'title', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return $notifications->toArray();
    }

    /**
     * Get top 5 users currently online.
     */
    public function getOnlineUsers(): array
    {
        $onlineThreshold = now()->subMinutes(15); // Consider active if last activity within 15 minutes

        $onlineUsers = UserSession::with('user:id,name,email')
            ->where('is_active', true)
            ->where('last_activity_at', '>=', $onlineThreshold)
            ->orderBy('last_activity_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->user->id,
                    'name' => $session->user->name,
                    'email' => $session->user->email,
                    'last_activity' => $session->last_activity_at->diffForHumans(),
                    'login_time' => $session->login_at->diffForHumans(),
                ];
            });

        return $onlineUsers->toArray();
    }

    /**
     * Get top 5 frequently logged-in users in the last month.
     */
    public function getFrequentUsers(): array
    {
        $frequentUsers = UserSession::with('user:id,name,email')
            ->select('user_id', DB::raw('COUNT(*) as login_count'))
            ->where('login_at', '>=', now()->subMonth())
            ->groupBy('user_id')
            ->orderBy('login_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->user->id,
                    'name' => $session->user->name,
                    'email' => $session->user->email,
                    'login_count' => $session->login_count,
                ];
            });

        return $frequentUsers->toArray();
    }

    /**
     * Get top 5 frequently visited content/menus.
     */
    public function getFrequentContent(): array
    {
        $frequentContent = ContentVisit::select(
                'page_type',
                'content_id',
                'menu_id',
                'page_title',
                'page_url',
                DB::raw('COUNT(*) as visit_count')
            )
            ->where('visited_at', '>=', now()->subMonth())
            ->groupBy(['page_type', 'content_id', 'menu_id', 'page_title', 'page_url'])
            ->orderBy('visit_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($visit) {
                return [
                    'type' => $visit->page_type,
                    'title' => $visit->page_title ?: 'Untitled',
                    'url' => $visit->page_url,
                    'visit_count' => $visit->visit_count,
                ];
            });

        return $frequentContent->toArray();
    }

    /**
     * Get marquee text configuration.
     */
    public function getMarqueeText(): array
    {
        $marqueeConfig = SystemConfiguration::getByKey('dashboard_marquee', []);
        
        // Default marquee text if not configured
        if (empty($marqueeConfig)) {
            $marqueeConfig = [
                'text' => 'Welcome to Indonet Analytics Hub - Your comprehensive data analytics platform',
                'speed' => 'normal', // slow, normal, fast
                'enabled' => true,
            ];
        }

        return $marqueeConfig;
    }

    /**
     * Update jumbotron configuration (Admin only).
     */
    public function updateJumbotron(Request $request): JsonResponse
    {
        $request->validate([
            'slides' => 'required|array|min:1',
            'slides.*.title' => 'required|string|max:255',
            'slides.*.subtitle' => 'nullable|string|max:500',
            'slides.*.image' => 'required|string',
            'slides.*.button_text' => 'nullable|string|max:100',
            'slides.*.button_link' => 'nullable|string|max:255',
            'settings' => 'required|array',
            'settings.autoplay' => 'boolean',
            'settings.interval' => 'integer|min:1000|max:10000',
            'settings.indicators' => 'boolean',
            'settings.controls' => 'boolean',
        ]);

        SystemConfiguration::setByKey(
            'dashboard_jumbotron',
            $request->all(),
            'json',
            'Dashboard jumbotron carousel configuration'
        );

        return response()->json([
            'success' => true,
            'message' => 'Jumbotron configuration updated successfully',
        ]);
    }

    /**
     * Update marquee text configuration (Admin only).
     */
    public function updateMarquee(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:1000',
            'speed' => 'required|in:slow,normal,fast',
            'enabled' => 'boolean',
        ]);

        SystemConfiguration::setByKey(
            'dashboard_marquee',
            $request->all(),
            'json',
            'Dashboard marquee text configuration'
        );

        return response()->json([
            'success' => true,
            'message' => 'Marquee configuration updated successfully',
        ]);
    }
}
