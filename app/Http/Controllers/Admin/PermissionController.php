<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Permission Management Controller
 * 
 * Handles CRUD operations for permissions in the admin panel
 */
class PermissionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Permission::class, 'permission');
    }
    /**
     * Display a listing of permissions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Permission::query();

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
            $permissions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $permissions,
                'message' => 'Permissions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created permission.
     *
     * @param PermissionRequest $request
     * @return JsonResponse
     */
    public function store(PermissionRequest $request): JsonResponse
    {
        try {
            $permission = Permission::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => $permission,
                'message' => 'Permission created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified permission.
     *
     * @param Permission $permission
     * @return JsonResponse
     */
    public function show(Permission $permission): JsonResponse
    {
        try {
            $permission->load('roles');

            return response()->json([
                'success' => true,
                'data' => $permission,
                'message' => 'Permission retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified permission.
     *
     * @param PermissionRequest $request
     * @param Permission $permission
     * @return JsonResponse
     */
    public function update(PermissionRequest $request, Permission $permission): JsonResponse
    {
        try {
            $permission->update($request->validated());

            return response()->json([
                'success' => true,
                'data' => $permission,
                'message' => 'Permission updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified permission.
     *
     * @param Permission $permission
     * @return JsonResponse
     */
    public function destroy(Permission $permission): JsonResponse
    {
        try {
            // Check if permission is assigned to any roles
            $rolesCount = $permission->roles()->count();
            
            if ($rolesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete permission. It is assigned to {$rolesCount} role(s). Please remove it from all roles first."
                ], 409);
            }

            $permission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
