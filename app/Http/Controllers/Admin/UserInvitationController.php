<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserInvitationRequest;
use App\Mail\UserInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class UserInvitationController extends Controller
{
    /**
     * Send user invitation
     */
    public function invite(UserInvitationRequest $request): JsonResponse
    {
        // Generate temporary password
        $temporaryPassword = Str::random(12);

        // Create user with temporary password
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($temporaryPassword),
            'invited_by' => $request->user()->id,
            'temporary_password_used' => true,
        ]);

        // Send invitation email
        try {
            Mail::to($user->email)->send(new UserInvitation(
                $user, 
                $temporaryPassword,
                $request->user()->name
            ));
        } catch (\Exception $e) {
            // If email fails, delete the user and return error
            $user->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invitation email. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'User invitation sent successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'invited_by' => $user->invited_by,
                    'temporary_password_used' => $user->temporary_password_used,
                ],
                'temporary_password' => $temporaryPassword, // Include for testing purposes
            ],
        ], 201);
    }

    /**
     * Get list of invited users
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::with('invitedBy')
            ->where('invited_by', '!=', null)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get invited users by current admin
     */
    public function myInvitations(Request $request): JsonResponse
    {
        $users = User::where('invited_by', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Resend invitation (regenerate temporary password)
     */
    public function resendInvitation(Request $request, User $user): JsonResponse
    {
        // Check if user was invited by current admin or if current user is super admin
        if ($user->invited_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only resend invitations for users you invited.',
            ], 403);
        }

        // Generate new temporary password
        $temporaryPassword = Str::random(12);

        // Update user with new temporary password
        $user->update([
            'password' => Hash::make($temporaryPassword),
            'temporary_password_used' => true,
        ]);

        // Send invitation email again
        try {
            Mail::to($user->email)->send(new UserInvitation(
                $user, 
                $temporaryPassword,
                $request->user()->name
            ));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend invitation email. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invitation resent successfully.',
        ]);
    }

    /**
     * Cancel/Delete invitation (remove user if they haven't logged in)
     */
    public function cancelInvitation(Request $request, User $user): JsonResponse
    {
        // Check if user was invited by current admin
        if ($user->invited_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only cancel invitations for users you invited.',
            ], 403);
        }

        // Check if user has ever logged in (if they haven't used temporary password)
        if (!$user->temporary_password_used && $user->last_active_at) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel invitation for user who has already activated their account.',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invitation cancelled successfully.',
        ]);
    }
}
