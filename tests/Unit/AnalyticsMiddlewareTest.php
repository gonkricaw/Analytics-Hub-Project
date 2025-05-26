<?php

namespace Tests\Unit;

use App\Http\Middleware\TrackUserSessions;
use App\Http\Middleware\TrackContentVisits;
use App\Models\User;
use App\Models\UserSession;
use App\Models\ContentVisit;
use App\Models\Content;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class AnalyticsMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test TrackUserSessions middleware creates session for authenticated user
     */
    public function test_track_user_sessions_creates_session_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $request = Request::create('/api/test', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Mock session
        $session = \Mockery::mock(\Illuminate\Contracts\Session\Session::class);
        $session->shouldReceive('getId')->andReturn('test-session-id');
        $request->setLaravelSession($session);

        $middleware = new TrackUserSessions();
        
        $this->assertDatabaseCount('idnbi_user_sessions', 0);

        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertDatabaseCount('idnbi_user_sessions', 1);
        
        $userSession = UserSession::first();
        $this->assertEquals($user->id, $userSession->user_id);
        $this->assertEquals('test-session-id', $userSession->session_id);
        $this->assertTrue($userSession->is_active);
    }

    /**
     * Test TrackUserSessions middleware does not create session for unauthenticated user
     */
    public function test_track_user_sessions_skips_unauthenticated_user(): void
    {
        $request = Request::create('/api/test', 'GET');
        $request->setUserResolver(function () {
            return null;
        });

        $middleware = new TrackUserSessions();
        
        $this->assertDatabaseCount('idnbi_user_sessions', 0);

        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertDatabaseCount('idnbi_user_sessions', 0);
    }

    /**
     * Test TrackUserSessions middleware updates existing active session
     */
    public function test_track_user_sessions_updates_existing_session(): void
    {
        $user = User::factory()->create();
        
        // Create existing session
        $existingSession = UserSession::create([
            'user_id' => $user->id,
            'session_id' => 'test-session-id',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Old Agent',
            'login_at' => now()->subMinutes(10),
            'last_activity_at' => now()->subMinutes(10),
            'is_active' => true,
        ]);

        $request = Request::create('/api/test', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        // Mock session
        $session = \Mockery::mock(\Illuminate\Contracts\Session\Session::class);
        $session->shouldReceive('getId')->andReturn('test-session-id');
        $request->setLaravelSession($session);

        // Override IP and user agent
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $request->headers->set('User-Agent', 'New Agent');

        $middleware = new TrackUserSessions();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response();
        });

        // Should still have only one session record
        $this->assertDatabaseCount('idnbi_user_sessions', 1);
        
        $existingSession->refresh();
        $this->assertEquals('127.0.0.1', $existingSession->ip_address);
        $this->assertEquals('New Agent', $existingSession->user_agent);
        $this->assertTrue($existingSession->last_activity_at->gt($existingSession->login_at));
    }

    /**
     * Test TrackUserSessions::markSessionLogout static method
     */
    public function test_mark_session_logout(): void
    {
        $user = User::factory()->create();
        
        $session = UserSession::create([
            'user_id' => $user->id,
            'session_id' => 'test-session-id',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'login_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);

        $this->assertTrue($session->is_active);
        $this->assertNull($session->logout_at);

        TrackUserSessions::markSessionLogout('test-session-id', $user->id);

        $session->refresh();
        $this->assertFalse($session->is_active);
        $this->assertNotNull($session->logout_at);
    }

    /**
     * Test TrackUserSessions::cleanupOldSessions static method
     */
    public function test_cleanup_old_sessions(): void
    {
        $user = User::factory()->create();
        
        // Create old inactive session (should remain unchanged)
        $oldSession = UserSession::create([
            'user_id' => $user->id,
            'session_id' => 'old-session',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'login_at' => now()->subHours(5),
            'last_activity_at' => now()->subHours(5),
            'is_active' => true,
        ]);

        // Create recent active session (should remain active)
        $recentSession = UserSession::create([
            'user_id' => $user->id,
            'session_id' => 'recent-session',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'login_at' => now()->subMinutes(30),
            'last_activity_at' => now()->subMinutes(30),
            'is_active' => true,
        ]);

        TrackUserSessions::cleanupOldSessions();

        $oldSession->refresh();
        $recentSession->refresh();

        $this->assertFalse($oldSession->is_active);
        $this->assertNotNull($oldSession->logout_at);
        
        $this->assertTrue($recentSession->is_active);
        $this->assertNull($recentSession->logout_at);
    }

    /**
     * Test TrackContentVisits middleware tracks content visits
     */
    public function test_track_content_visits_tracks_content(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['slug' => 'test-content', 'title' => 'Test Content']);
        
        $request = Request::create('/api/contents/test-content', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new TrackContentVisits();
        
        $this->assertDatabaseCount('idnbi_content_visits', 0);

        $response = $middleware->handle($request, function ($req) {
            return new Response('', 200);
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertDatabaseCount('idnbi_content_visits', 1);
        
        $visit = ContentVisit::first();
        $this->assertEquals($user->id, $visit->user_id);
        $this->assertEquals($content->id, $visit->content_id);
        $this->assertEquals('content', $visit->page_type);
        $this->assertEquals('Test Content', $visit->page_title);
    }

    /**
     * Test TrackContentVisits middleware tracks menu visits
     */
    public function test_track_content_visits_tracks_menu(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create(['name' => 'Test Menu']);
        
        $request = Request::create('/api/menus/' . $menu->id, 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new TrackContentVisits();
        
        $this->assertDatabaseCount('idnbi_content_visits', 0);

        $response = $middleware->handle($request, function ($req) {
            return new Response('', 200);
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertDatabaseCount('idnbi_content_visits', 1);
        
        $visit = ContentVisit::first();
        $this->assertEquals($user->id, $visit->user_id);
        $this->assertEquals($menu->id, $visit->menu_id);
        $this->assertEquals('menu', $visit->page_type);
        $this->assertEquals('Test Menu', $visit->page_title);
    }

    /**
     * Test TrackContentVisits middleware skips unauthenticated users
     */
    public function test_track_content_visits_skips_unauthenticated(): void
    {
        $request = Request::create('/api/contents/test', 'GET');
        $request->setUserResolver(function () {
            return null;
        });

        $middleware = new TrackContentVisits();
        
        $this->assertDatabaseCount('idnbi_content_visits', 0);

        $response = $middleware->handle($request, function ($req) {
            return new Response('', 200);
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertDatabaseCount('idnbi_content_visits', 0);
    }

    /**
     * Test TrackContentVisits middleware skips error responses
     */
    public function test_track_content_visits_skips_error_responses(): void
    {
        $user = User::factory()->create();
        
        $request = Request::create('/api/contents/test', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new TrackContentVisits();
        
        $this->assertDatabaseCount('idnbi_content_visits', 0);

        $response = $middleware->handle($request, function ($req) {
            return new Response('', 404);
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertDatabaseCount('idnbi_content_visits', 0);
    }

    /**
     * Test TrackContentVisits middleware skips certain paths
     */
    public function test_track_content_visits_skips_excluded_paths(): void
    {
        $user = User::factory()->create();
        
        $excludedPaths = [
            '/api/user',
            '/api/logout',
            '/api/dashboard',
            '/sanctum/csrf-cookie'
        ];

        $middleware = new TrackContentVisits();

        foreach ($excludedPaths as $path) {
            $request = Request::create($path, 'GET');
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            $response = $middleware->handle($request, function ($req) {
                return new Response('', 200);
            });

            $this->assertInstanceOf(Response::class, $response);
        }

        // Should have no visits tracked for excluded paths
        $this->assertDatabaseCount('idnbi_content_visits', 0);
    }

    /**
     * Test TrackContentVisits::updateVisitDuration static method
     */
    public function test_update_visit_duration(): void
    {
        $user = User::factory()->create();
        
        $visit = ContentVisit::create([
            'user_id' => $user->id,
            'page_type' => 'content',
            'page_title' => 'Test Page',
            'page_url' => 'http://localhost/test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'visited_at' => now(),
            'duration_seconds' => 0,
        ]);

        $this->assertEquals(0, $visit->duration_seconds);

        $result = TrackContentVisits::updateVisitDuration($visit->id, 150);
        
        $this->assertTrue($result);
        
        $visit->refresh();
        $this->assertEquals(150, $visit->duration_seconds);
    }

    /**
     * Test TrackContentVisits::getPopularContent static method
     */
    public function test_get_popular_content(): void
    {
        $user = User::factory()->create();
        $content1 = Content::factory()->create(['title' => 'Popular Content']);
        $content2 = Content::factory()->create(['title' => 'Less Popular Content']);

        // Create visits for content1 (more popular)
        for ($i = 0; $i < 5; $i++) {
            ContentVisit::create([
                'user_id' => $user->id,
                'content_id' => $content1->id,
                'page_type' => 'content',
                'page_title' => $content1->title,
                'page_url' => 'http://localhost/content1',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'visited_at' => now()->subDays(rand(1, 10)),
                'duration_seconds' => 60,
            ]);
        }

        // Create visits for content2 (less popular)
        for ($i = 0; $i < 2; $i++) {
            ContentVisit::create([
                'user_id' => $user->id,
                'content_id' => $content2->id,
                'page_type' => 'content',
                'page_title' => $content2->title,
                'page_url' => 'http://localhost/content2',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'visited_at' => now()->subDays(rand(1, 10)),
                'duration_seconds' => 60,
            ]);
        }

        $popularContent = TrackContentVisits::getPopularContent(10, 30);

        $this->assertCount(2, $popularContent);
        
        // Should be ordered by visit count descending
        $this->assertEquals($content1->id, $popularContent[0]['content_id']);
        $this->assertEquals(5, $popularContent[0]['visit_count']);
        $this->assertEquals($content2->id, $popularContent[1]['content_id']);
        $this->assertEquals(2, $popularContent[1]['visit_count']);
    }

    /**
     * Test TrackContentVisits::getPopularMenus static method
     */
    public function test_get_popular_menus(): void
    {
        $user = User::factory()->create();
        $menu1 = Menu::factory()->create(['name' => 'Popular Menu']);
        $menu2 = Menu::factory()->create(['name' => 'Less Popular Menu']);

        // Create visits for menu1 (more popular)
        for ($i = 0; $i < 4; $i++) {
            ContentVisit::create([
                'user_id' => $user->id,
                'menu_id' => $menu1->id,
                'page_type' => 'menu',
                'page_title' => $menu1->name,
                'page_url' => 'http://localhost/menu1',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'visited_at' => now()->subDays(rand(1, 10)),
                'duration_seconds' => 60,
            ]);
        }

        // Create visits for menu2 (less popular)
        ContentVisit::create([
            'user_id' => $user->id,
            'menu_id' => $menu2->id,
            'page_type' => 'menu',
            'page_title' => $menu2->name,
            'page_url' => 'http://localhost/menu2',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'visited_at' => now()->subDays(rand(1, 10)),
            'duration_seconds' => 60,
        ]);

        $popularMenus = TrackContentVisits::getPopularMenus(10, 30);

        $this->assertCount(2, $popularMenus);
        
        // Should be ordered by visit count descending
        $this->assertEquals($menu1->id, $popularMenus[0]['menu_id']);
        $this->assertEquals(4, $popularMenus[0]['visit_count']);
        $this->assertEquals($menu2->id, $popularMenus[1]['menu_id']);
        $this->assertEquals(1, $popularMenus[1]['visit_count']);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
