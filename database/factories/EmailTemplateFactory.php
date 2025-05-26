<?php

namespace Database\Factories;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement([
            EmailTemplate::TYPE_INVITATION,
            EmailTemplate::TYPE_PASSWORD_RESET,
            EmailTemplate::TYPE_WELCOME,
            EmailTemplate::TYPE_NOTIFICATION,
            EmailTemplate::TYPE_GENERAL,
        ]);

        return [
            'name' => $this->faker->words(3, true) . ' Template',
            'type' => $type,
            'subject' => $this->faker->sentence() . ' {{app_name}}',
            'html_content' => $this->generateHtmlContent(),
            'text_content' => $this->generateTextContent(),
            'description' => $this->faker->paragraph(),
            'placeholders' => $this->generatePlaceholders(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'created_by_user_id' => User::factory(),
        ];
    }

    /**
     * Generate sample HTML content with placeholders.
     *
     * @return string
     */
    private function generateHtmlContent(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{subject}}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #667eea;">{{app_name}}</h1>
        
        <h2>Hello, {{user_name}}!</h2>
        
        <p>' . $this->faker->paragraph() . '</p>
        
        <p>' . $this->faker->paragraph() . '</p>
        
        <div style="margin: 30px 0;">
            <a href="{{action_url}}" style="background-color: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
                Take Action
            </a>
        </div>
        
        <p>Best regards,<br>
        The {{app_name}} Team</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        
        <p style="font-size: 12px; color: #666;">
            This email was sent to {{user_email}}. If you no longer wish to receive these emails, you can unsubscribe at any time.
        </p>
    </div>
</body>
</html>';
    }

    /**
     * Generate sample text content with placeholders.
     *
     * @return string
     */
    private function generateTextContent(): string
    {
        return '{{app_name}}

Hello, {{user_name}}!

' . $this->faker->paragraph() . '

' . $this->faker->paragraph() . '

Take Action: {{action_url}}

Best regards,
The {{app_name}} Team

---

This email was sent to {{user_email}}. If you no longer wish to receive these emails, you can unsubscribe at any time.';
    }

    /**
     * Generate sample placeholders array.
     *
     * @return array
     */
    private function generatePlaceholders(): array
    {
        return [
            'app_name',
            'user_name',
            'user_email',
            'action_url',
            'subject',
            'current_date',
            'current_year'
        ];
    }

    /**
     * Indicate that the template should be active.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the template should be inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    /**
     * Create a template of a specific type.
     *
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function ofType(string $type): Factory
    {
        return $this->state(function (array $attributes) use ($type) {
            return [
                'type' => $type,
                'name' => ucfirst(str_replace('_', ' ', $type)) . ' Template',
            ];
        });
    }

    /**
     * Create an invitation template.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function invitation(): Factory
    {
        return $this->ofType(EmailTemplate::TYPE_INVITATION)->state([
            'subject' => 'Welcome to {{app_name}} - Your Account Invitation',
            'html_content' => $this->generateInvitationHtml(),
            'text_content' => $this->generateInvitationText(),
        ]);
    }

    /**
     * Create a password reset template.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function passwordReset(): Factory
    {
        return $this->ofType(EmailTemplate::TYPE_PASSWORD_RESET)->state([
            'subject' => 'Reset Your {{app_name}} Password',
            'html_content' => $this->generatePasswordResetHtml(),
            'text_content' => $this->generatePasswordResetText(),
        ]);
    }

    /**
     * Generate invitation-specific HTML content.
     *
     * @return string
     */
    private function generateInvitationHtml(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #667eea;">{{app_name}}</h1>
        
        <h2>Welcome, {{user_name}}!</h2>
        
        <p>You have been invited to join <strong>{{app_name}}</strong> by {{invited_by}}.</p>
        
        <p>To get started, please use the following credentials to log in:</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <p><strong>Email:</strong> {{user_email}}</p>
            <p><strong>Temporary Password:</strong> {{temporary_password}}</p>
        </div>
        
        <div style="margin: 30px 0;">
            <a href="{{login_url}}" style="background-color: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
                Login to Your Account
            </a>
        </div>
        
        <p><strong>Important:</strong> You will be required to change your password upon first login for security purposes.</p>
        
        <p>Best regards,<br>
        The {{app_name}} Team</p>
    </div>
</body>
</html>';
    }

    /**
     * Generate invitation-specific text content.
     *
     * @return string
     */
    private function generateInvitationText(): string
    {
        return '{{app_name}}

Welcome, {{user_name}}!

You have been invited to join {{app_name}} by {{invited_by}}.

To get started, please use the following credentials to log in:

Email: {{user_email}}
Temporary Password: {{temporary_password}}

Login URL: {{login_url}}

Important: You will be required to change your password upon first login for security purposes.

Best regards,
The {{app_name}} Team';
    }

    /**
     * Generate password reset-specific HTML content.
     *
     * @return string
     */
    private function generatePasswordResetHtml(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #667eea;">{{app_name}}</h1>
        
        <h2>Password Reset Request</h2>
        
        <p>Hello {{user_name}},</p>
        
        <p>We received a request to reset your password for your {{app_name}} account.</p>
        
        <div style="margin: 30px 0;">
            <a href="{{reset_link}}" style="background-color: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
                Reset Your Password
            </a>
        </div>
        
        <p>This link will expire in {{expiry_time}} for security reasons.</p>
        
        <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
        
        <p>Best regards,<br>
        The {{app_name}} Team</p>
    </div>
</body>
</html>';
    }

    /**
     * Generate password reset-specific text content.
     *
     * @return string
     */
    private function generatePasswordResetText(): string
    {
        return '{{app_name}}

Password Reset Request

Hello {{user_name}},

We received a request to reset your password for your {{app_name}} account.

Reset your password: {{reset_link}}

This link will expire in {{expiry_time}} for security reasons.

If you did not request a password reset, please ignore this email or contact support if you have concerns.

Best regards,
The {{app_name}} Team';
    }
}
