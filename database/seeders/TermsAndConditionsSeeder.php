<?php

namespace Database\Seeders;

use App\Models\TermsAndConditions;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TermsAndConditionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a system admin user if one doesn't exist
        $systemAdmin = User::firstOrCreate(
            ['email' => 'admin@indonet.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('admin123'),
                'temporary_password_used' => false,
            ]
        );

        // Create initial Terms and Conditions
        $initialTerms = TermsAndConditions::firstOrCreate(
            ['version' => '1.0'],
            [
                'content' => $this->getInitialTermsContent(),
                'is_active' => true,
                'created_by' => $systemAdmin->id,
            ]
        );

        echo "✅ Initial Terms and Conditions (v1.0) created successfully.\n";
        echo "✅ System Administrator account created (admin@indonet.com / admin123).\n";
    }

    /**
     * Get the initial terms and conditions content
     */
    private function getInitialTermsContent(): string
    {
        return "
# Terms and Conditions - Indonet Analytics Hub

## 1. Acceptance of Terms
By accessing and using the Indonet Analytics Hub platform, you agree to be bound by these Terms and Conditions.

## 2. User Accounts
- Users must provide accurate and complete information when creating accounts
- Users are responsible for maintaining the security of their login credentials
- Temporary passwords must be changed upon first login

## 3. Data Privacy and Security
- All user data is protected according to our Privacy Policy
- Users must not attempt to access unauthorized data or systems
- Failed login attempts are monitored for security purposes

## 4. Platform Usage
- The platform is intended for authorized business analytics purposes only
- Users must comply with all applicable laws and regulations
- Misuse of the platform may result in account termination

## 5. Intellectual Property
- All content and software on the platform is proprietary to Indonet
- Users may not reproduce or distribute platform content without permission

## 6. Limitation of Liability
- The platform is provided 'as is' without warranties
- Indonet shall not be liable for any indirect or consequential damages

## 7. Modifications
- These terms may be updated from time to time
- Users will be notified of significant changes
- Continued use constitutes acceptance of modified terms

## 8. Contact Information
For questions about these terms, please contact: powerbi@indonet.id

Last Updated: May 25, 2025
Version: 1.0
        ";
    }
}
