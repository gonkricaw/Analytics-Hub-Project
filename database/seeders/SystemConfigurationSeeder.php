<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemConfiguration;

class SystemConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dashboard Jumbotron Configuration
        SystemConfiguration::setByKey(
            'dashboard_jumbotron',
            [
                'slides' => [
                    [
                        'id' => 1,
                        'title' => 'Welcome to Indonet Analytics Hub',
                        'subtitle' => 'Your comprehensive analytics platform for data-driven insights',
                        'image' => '/images/hero/hero-bg.jpg',
                        'button_text' => 'Explore Dashboard',
                        'button_link' => '#',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Powerful Analytics & Reporting',
                        'subtitle' => 'Track user activity, content performance, and system metrics',
                        'image' => '/images/hero/hero-bg-2.jpg',
                        'button_text' => 'View Reports',
                        'button_link' => '#',
                    ],
                    [
                        'id' => 3,
                        'title' => 'Secure Content Management',
                        'subtitle' => 'Manage and deliver content with role-based access control',
                        'image' => '/images/hero/hero-bg-3.jpg',
                        'button_text' => 'Manage Content',
                        'button_link' => '#',
                    ],
                ],
                'settings' => [
                    'autoplay' => true,
                    'interval' => 5000,
                    'indicators' => true,
                    'controls' => true,
                ],
            ],
            'json',
            'Dashboard jumbotron carousel configuration',
            true
        );

        // Dashboard Marquee Configuration
        SystemConfiguration::setByKey(
            'dashboard_marquee',
            [
                'text' => '🚀 Welcome to Indonet Analytics Hub - Your premier destination for comprehensive data analytics and content management! 📊 Track performance, manage users, and deliver content securely! 🔒',
                'speed' => 'normal',
                'enabled' => true,
            ],
            'json',
            'Dashboard marquee text configuration',
            true
        );

        // Application Configuration
        SystemConfiguration::setByKey(
            'app_name',
            'Indonet Analytics Hub',
            'text',
            'Application name displayed in headers and titles',
            true
        );

        SystemConfiguration::setByKey(
            'app_logo',
            '/images/logo/logo.png',
            'text',
            'Application logo path',
            true
        );

        SystemConfiguration::setByKey(
            'default_profile_photo',
            '/images/avatars/default-avatar.png',
            'text',
            'Default profile photo for new users',
            true
        );

        SystemConfiguration::setByKey(
            'login_background',
            '/images/backgrounds/login-bg.jpg',
            'text',
            'Login page background image',
            true
        );

        SystemConfiguration::setByKey(
            'app_footer',
            '© 2025 Indonet Analytics Hub. All rights reserved.',
            'text',
            'Application footer text',
            true
        );

        // Dashboard Settings
        SystemConfiguration::setByKey(
            'dashboard_refresh_interval',
            30,
            'text',
            'Dashboard auto-refresh interval in seconds',
            false
        );

        SystemConfiguration::setByKey(
            'dashboard_clock_format',
            '24h',
            'text',
            'Dashboard digital clock format (12h or 24h)',
            true
        );
    }
}
