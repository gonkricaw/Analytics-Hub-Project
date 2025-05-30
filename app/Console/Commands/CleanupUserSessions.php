<?php

namespace App\Console\Commands;

use App\Http\Middleware\TrackUserSessions;
use App\Models\UserSession;
use Illuminate\Console\Command;

class CleanupUserSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup {--hours=2 : Hours of inactivity before marking session as inactive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old inactive user sessions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = $this->option('hours');
        
        $this->info("Cleaning up user sessions inactive for more than {$hours} hours...");
        
        // Get count of sessions to be cleaned up
        $sessionsToCleanup = UserSession::where('is_active', true)
            ->where('last_activity_at', '<', now()->subHours($hours))
            ->count();
            
        if ($sessionsToCleanup === 0) {
            $this->info('No sessions found that need cleanup.');
            return self::SUCCESS;
        }
        
        // Perform cleanup
        TrackUserSessions::cleanupOldSessions();
        
        $this->info("Successfully cleaned up {$sessionsToCleanup} inactive sessions.");
        
        // Also cleanup very old session records (older than 30 days)
        $oldSessions = UserSession::where('created_at', '<', now()->subDays(30))->count();
        
        if ($oldSessions > 0) {
            UserSession::where('created_at', '<', now()->subDays(30))->delete();
            $this->info("Deleted {$oldSessions} session records older than 30 days.");
        }
        
        return self::SUCCESS;
    }
}
