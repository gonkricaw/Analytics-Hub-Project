<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Role Management Controller
 * 
 * Handles CRUD operations for roles in the admin panel
 */
class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }
    /**
     * Display a listing of roles.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Role::with('permissions');

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'ILIKE', "%{$searchTerm}%")
                      ->orWhere('display_name', 'ILIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'ILIKE', "%{$searchTerm}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'display_name');
            $sortOrder = $request->get('sort_order', 'asc');
            
            $allowedSortFields = ['name', 'display_name', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $roles = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'Roles retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role.
     *
     * @param RoleRequest $request
     * @return JsonResponse
     */
    public function store(RoleRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $permissionIds = $validated['permission_ids'] ?? [];
            
            // Remove permission_ids from validated data for role creation
            unset($validated['permission_ids']);

            $role = Role::create($validated);

            // Sync permissions if provided
            if (!empty($permissionIds)) {
                $role->permissions()->sync($permissionIds);
            }

            $role->load('permissions');

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Role created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role.
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function show(Role $role): JsonResponse
    {
        try {
            $role->load(['permissions', 'users']);

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Role retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role.
     *
     * @param RoleRequest $request
     * @param Role $role
     * @return JsonResponse
     */
    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        try {
            $validated = $request->validated();
            $permissionIds = $validated['permission_ids'] ?? [];
            
            // Remove permission_ids from validated data for role update
            unset($validated['permission_ids']);

            $role->update($validated);

            // Sync permissions if provided
            if (isset($request->permission_ids)) {
                $role->permissions()->sync($permissionIds);
            }

            $role->load('permissions');

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Role updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role.
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            // Check if role is assigned to any users
            $usersCount = $role->users()->count();
            
            if ($usersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete role. It is assigned to {$usersCount} user(s). Please remove it from all users first."
                ], 409);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to a role.
     *
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function assignPermissions(Request $request, Role $role): JsonResponse
    {
        try {
            $request->validate([
                'permission_ids' => 'required|array',
                'permission_ids.*' => 'exists:idnbi_permissions,id',
            ]);

            $role->permissions()->sync($request->permission_ids);
            $role->load('permissions');

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Permissions assigned to role successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions to role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
