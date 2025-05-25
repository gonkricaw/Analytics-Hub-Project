<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * User Role Assignment Controller
 * 
 * Handles role assignment and management for users
 */
class UserRoleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    /**
     * Get roles for a specific user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function getUserRoles(User $user): JsonResponse
    {
        $this->authorize('view', [User::class, $user]);

        try {
            $user->load('roles.permissions');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'roles' => $user->roles,
                    'permissions' => $user->getAllPermissions()
                ],
                'message' => 'User roles retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign/sync roles to a user.
     *
     * @param UserRoleRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function syncRoles(UserRoleRequest $request, User $user): JsonResponse
    {
        $this->authorize('syncRoles', [User::class, $user]);

        try {
            $user->roles()->sync($request->role_ids);
            $user->load('roles.permissions');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'roles' => $user->roles,
                ],
                'message' => 'User roles updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a single role to a user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function assignRole(Request $request, User $user): JsonResponse
    {
        $this->authorize('assignRole', [User::class, $user]);

        $request->validate([
            'role_id' => 'required|exists:idnbi_roles,id'
        ]);

        try {
            $user->roles()->attach($request->role_id);
            $user->load('roles.permissions');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'roles' => $user->roles,
                ],
                'message' => 'Role assigned successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a single role from a user.
     *
     * @param User $user
     * @param Role $role
     * @return JsonResponse
     */
    public function removeRole(User $user, $role): JsonResponse
    {
        $this->authorize('removeRole', [User::class, $user]);

        try {
            $user->roles()->detach($role);
            $user->load('roles.permissions');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'roles' => $user->roles,
                ],
                'message' => 'Role removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users with their roles for admin management.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        try {
            $query = User::with('roles');

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            }

            // Filter by role
            if ($request->has('role_id') && !empty($request->role_id)) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('idnbi_roles.id', $request->role_id);
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            
            $allowedSortFields = ['name', 'email', 'created_at', 'last_active_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'Users with roles retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users with roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
