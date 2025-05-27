<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\User;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the first admin user or create a system user
        $adminUser = User::where('email', 'admin@example.com')->first();
        
        if (!$adminUser) {
            // Create a system user if no admin exists
            $adminUser = User::create([
                'name' => 'System Administrator',
                'email' => 'system@analytics-hub.com',
                'password' => bcrypt('system-password'),
                'email_verified_at' => now(),
            ]);
        }

        $templates = [
            [
                'name' => 'User Invitation Template',
                'type' => EmailTemplate::TYPE_INVITATION,
                'subject' => 'Welcome to {{app_name}} - Your Account Invitation',
                'html_content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #667eea;">{{app_name}}</h1>
        </div>
        
        <h2>Welcome, {{user_name}}!</h2>
        
        <p>You have been invited to join <strong>{{app_name}}</strong> by {{invited_by}}.</p>
        
        <p>To get started, please use the following credentials to log in:</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Email:</strong> {{user_email}}</p>
            <p><strong>Temporary Password:</strong> {{temporary_password}}</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{login_url}}" style="background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Login to Your Account</a>
        </div>
        
        <p><strong>Important:</strong> You will be required to change your password upon first login.</p>
        
        <p>If you have any questions, please contact our support team.</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="color: #666; font-size: 12px;">
            This email was sent from {{app_name}}. If you did not expect this invitation, please ignore this email.
        </p>
    </div>
</body>
</html>',
                'text_content' => 'Welcome to {{app_name}}!

You have been invited to join {{app_name}} by {{invited_by}}.

Login Credentials:
Email: {{user_email}}
Temporary Password: {{temporary_password}}

Please visit {{login_url}} to log in to your account.

Important: You will be required to change your password upon first login.

If you have any questions, please contact our support team.',
                'description' => 'Template for sending user invitations with temporary login credentials',
                'placeholders' => ['app_name', 'user_name', 'user_email', 'invited_by', 'temporary_password', 'login_url'],
                'is_active' => true,
                'created_by_user_id' => $adminUser->id,
            ],
            [
                'name' => 'Password Reset Template',
                'type' => EmailTemplate::TYPE_PASSWORD_RESET,
                'subject' => 'Reset Your {{app_name}} Password',
                'html_content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #667eea;">{{app_name}}</h1>
        </div>
        
        <h2>Password Reset Request</h2>
        
        <p>Hello {{user_name}},</p>
        
        <p>You are receiving this email because we received a password reset request for your account.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{reset_url}}" style="background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>
        </div>
        
        <p>This password reset link will expire in 60 minutes.</p>
        
        <p>If you did not request a password reset, no further action is required.</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="color: #666; font-size: 12px;">
            If you\'re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: {{reset_url}}
        </p>
    </div>
</body>
</html>',
                'text_content' => 'Password Reset Request

Hello {{user_name}},

You are receiving this email because we received a password reset request for your account.

Click the link below to reset your password:
{{reset_url}}

This password reset link will expire in 60 minutes.

If you did not request a password reset, no further action is required.',
                'description' => 'Template for password reset emails with secure reset links',
                'placeholders' => ['app_name', 'user_name', 'reset_url'],
                'is_active' => true,
                'created_by_user_id' => $adminUser->id,
            ],
            [
                'name' => 'Welcome Message Template',
                'type' => EmailTemplate::TYPE_WELCOME,
                'subject' => 'Welcome to {{app_name}}!',
                'html_content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #667eea;">{{app_name}}</h1>
        </div>
        
        <h2>Welcome, {{user_name}}!</h2>
        
        <p>We are excited to have you on board with <strong>{{app_name}}</strong>!</p>
        
        <p>Your account has been successfully created and you can now access all the features available to you.</p>
        
        <h3>What you can do next:</h3>
        <ul>
            <li>📊 Explore your personalized dashboard</li>
            <li>📈 Create and view analytics reports</li>
            <li>🔍 Use advanced data filtering tools</li>
            <li>👥 Collaborate with your team members</li>
            <li>⚙️ Customize your account settings</li>
        </ul>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{dashboard_url}}" style="background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Access Your Dashboard</a>
        </div>
        
        <p>If you need any help getting started, our support team is here to assist you.</p>
        
        <p>Welcome aboard!</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="color: #666; font-size: 12px;">
            Thank you for joining {{app_name}}. We look forward to helping you achieve your analytics goals.
        </p>
    </div>
</body>
</html>',
                'text_content' => 'Welcome to {{app_name}}!

Hello {{user_name}},

We are excited to have you on board with {{app_name}}!

Your account has been successfully created and you can now access all the features available to you.

What you can do next:
- 📊 Explore your personalized dashboard
- 📈 Create and view analytics reports
- 🔍 Use advanced data filtering tools
- 👥 Collaborate with your team members
- ⚙️ Customize your account settings

Visit your dashboard: {{dashboard_url}}

If you need any help getting started, our support team is here to assist you.

Welcome aboard!',
                'description' => 'Welcome email template for new users who have completed registration',
                'placeholders' => ['app_name', 'user_name', 'dashboard_url'],
                'is_active' => true,
                'created_by_user_id' => $adminUser->id,
            ],
            [
                'name' => 'Notification Template',
                'type' => EmailTemplate::TYPE_NOTIFICATION,
                'subject' => 'New Notification from {{app_name}}',
                'html_content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #667eea;">{{app_name}}</h1>
        </div>
        
        <h2>{{notification_title}}</h2>
        
        <p>Hello {{user_name}},</p>
        
        <p>You have received a new notification:</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 4px solid #667eea; margin: 20px 0;">
            {{notification_content}}
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{app_url}}/notifications" style="background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View All Notifications</a>
        </div>
        
        <p>You can manage your notification preferences in your account settings.</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="color: #666; font-size: 12px;">
            This notification was sent from {{app_name}}. You can update your email preferences in your account settings.
        </p>
    </div>
</body>
</html>',
                'text_content' => 'New Notification from {{app_name}}

Hello {{user_name}},

{{notification_title}}

{{notification_content}}

You can view all your notifications at: {{app_url}}/notifications

You can manage your notification preferences in your account settings.',
                'description' => 'Template for sending system notifications via email',
                'placeholders' => ['app_name', 'user_name', 'notification_title', 'notification_content', 'app_url'],
                'is_active' => true,
                'created_by_user_id' => $adminUser->id,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                [
                    'type' => $template['type'],
                    'name' => $template['name']
                ],
                $template
            );
        }

        $this->command->info('Email templates seeded successfully!');
    }
}
