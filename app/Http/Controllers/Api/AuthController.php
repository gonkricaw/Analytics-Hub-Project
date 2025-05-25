<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\PasswordReset;
use App\Mail\WelcomeUser;
use App\Models\User;
use App\Models\FailedLoginAttempt;
use App\Models\IpBlock;
use App\Models\TermsAndConditions;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Maximum failed login attempts before IP blocking
     */
    private const MAX_FAILED_ATTEMPTS = 5;
    
    /**
     * Time window for failed attempts (in minutes)
     */
    private const FAILED_ATTEMPTS_WINDOW = 60;

    /**
     * Handle user login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->email;
        $password = $request->password;
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Check if IP is blocked
        if (IpBlock::isBlocked($ipAddress)) {
            return response()->json([
                'success' => false,
                'message' => 'Your IP address has been blocked due to suspicious activity. Please contact the administrator.',
            ], 423);
        }

        // Check rate limiting
        $key = 'login.' . $ipAddress;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        // Find user
        $user = User::where('email', $email)->first();

        // Check credentials
        if (!$user || !Hash::check($password, $user->password)) {
            // Log failed attempt
            FailedLoginAttempt::create([
                'user_id' => $user?->id,
                'ip_address' => $ipAddress,
                'email' => $email,
                'user_agent' => $userAgent,
            ]);

            // Check if we should block the IP
            $this->checkAndBlockIp($ipAddress, $email);

            RateLimiter::hit($key);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Clear rate limiting on successful login
        RateLimiter::clear($key);

        // Update last active
        $user->updateLastActive();

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Check if user needs to change password
        $needsPasswordChange = $user->needsPasswordChange();

        // Check for active terms that need acceptance
        $currentTerms = TermsAndConditions::getCurrent();
        $needsTermsAcceptance = $currentTerms && !$user->terms_accepted_at;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_path' => $user->profile_photo_path,
                ],
                'token' => $token,
                'needs_password_change' => $needsPasswordChange,
                'needs_terms_acceptance' => $needsTermsAcceptance,
                'current_terms_version' => $currentTerms?->version,
            ],
        ]);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        $currentToken = $request->user()->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get authenticated user information
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->updateLastActive();

        $currentTerms = TermsAndConditions::getCurrent();
        $needsTermsAcceptance = $currentTerms && !$user->terms_accepted_at;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_path' => $user->profile_photo_path,
                    'last_active_at' => $user->last_active_at,
                ],
                'needs_password_change' => $user->needsPasswordChange(),
                'needs_terms_acceptance' => $needsTermsAcceptance,
                'current_terms_version' => $currentTerms?->version,
            ],
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $isInitialChange = $request->boolean('is_initial_change');

        // For initial password change (temporary password), skip current password check
        if (!$isInitialChange) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.',
                ], 400);
            }
        }

        // Check if this is the first time changing from temporary password
        $wasUsingTemporaryPassword = $user->temporary_password_used;

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
            'temporary_password_used' => false,
        ]);

        // Send welcome email if this was their first password change from temporary
        if ($wasUsingTemporaryPassword && $isInitialChange) {
            try {
                Mail::to($user->email)->send(new WelcomeUser($user));
            } catch (\Exception $e) {
                // Log error but don't fail the password change
                \Log::warning('Failed to send welcome email to ' . $user->email . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Send password reset email
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Return success message even if user doesn't exist for security
            return response()->json([
                'success' => true,
                'message' => 'If an account with that email exists, we have sent password reset instructions.',
            ]);
        }

        // Generate reset token
        $resetToken = Str::random(64);
        
        // Store the reset token
        \DB::table('idnbi_password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($resetToken),
                'created_at' => now(),
            ]
        );

        // Create reset URL
        $resetUrl = config('app.url') . '/reset-password?token=' . $resetToken . '&email=' . urlencode($request->email);

        // Send password reset email
        try {
            Mail::to($user->email)->send(new PasswordReset($user, $resetToken, $resetUrl));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send password reset email. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'If an account with that email exists, we have sent password reset instructions.',
        ]);
    }

    /**
     * Reset password using token
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        // Find the reset token
        $resetRecord = \DB::table('idnbi_password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // Check if token is expired (60 minutes)
        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        $minutesDiff = abs(now()->diffInMinutes($createdAt));
        
        if ($minutesDiff > 60) {
            // Delete expired token
            \DB::table('idnbi_password_reset_tokens')->where('email', $request->email)->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Reset token has expired. Please request a new one.',
            ], 400);
        }

        // Find user and update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'temporary_password_used' => false, // Mark that temporary password is no longer in use
        ]);

        // Delete the reset token
        \DB::table('idnbi_password_reset_tokens')->where('email', $request->email)->delete();

        // Send welcome email if this is their first time setting a permanent password
        if ($user->temporary_password_used) {
            try {
                Mail::to($user->email)->send(new WelcomeUser($user));
            } catch (\Exception $e) {
                // Log error but don't fail the password reset
                \Log::warning('Failed to send welcome email to ' . $user->email . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. You can now log in with your new password.',
        ]);
    }

    /**
     * Accept terms and conditions
     */
    public function acceptTerms(Request $request): JsonResponse
    {
        $request->validate([
            'terms_version' => 'required|string',
        ]);

        $user = $request->user();
        $currentTerms = TermsAndConditions::getCurrent();

        if (!$currentTerms || $currentTerms->version !== $request->terms_version) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid terms version.',
            ], 400);
        }

        $user->update([
            'terms_accepted_at' => now(),
            'terms_accepted_version' => $request->terms_version,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions accepted.',
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        $data = [
            'name' => $request->name,
        ];

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $photo = $request->file('profile_photo');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('images/avatars'), $filename);
            $data['profile_photo_path'] = 'images/avatars/' . $filename;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_path' => $user->profile_photo_path,
                ],
            ],
        ]);
    }

    /**
     * Check and potentially block IP address based on failed attempts
     */
    private function checkAndBlockIp(string $ipAddress, string $email): void
    {
        $recentAttempts = FailedLoginAttempt::byIpAddress($ipAddress)
            ->recent(self::FAILED_ATTEMPTS_WINDOW)
            ->count();

        if ($recentAttempts >= self::MAX_FAILED_ATTEMPTS) {
            IpBlock::blockIp($ipAddress, "Automatic block after {$recentAttempts} failed login attempts");
        }
    }
}
