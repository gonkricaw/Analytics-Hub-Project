<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of all menus for management.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('menus.view');

        $query = Menu::with(['parent', 'children', 'content']);

        // Filter by parent_id if specified
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Filter by type if specified
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        $menus = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $menus,
            'message' => 'Menus retrieved successfully'
        ]);
    }

    /**
     * Store a newly created menu.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('menus.create');

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['list_menu', 'content_menu'])],
            'parent_id' => 'nullable|exists:idnbi_menus,id',
            'icon' => 'nullable|string|max:255',
            'route_or_url' => 'nullable|string|max:500',
            'content_id' => 'nullable|exists:idnbi_contents,id',
            'order' => 'integer|min:0',
            'role_permissions_required' => 'array',
            'role_permissions_required.*' => 'string',
        ]);

        // Validate content_id for content_menu type
        if ($request->type === 'content_menu' && !$request->content_id) {
            return response()->json([
                'success' => false,
                'message' => 'Content ID is required for content menu type'
            ], 422);
        }

        // Set default order if not provided
        if (!$request->has('order')) {
            $maxOrder = Menu::where('parent_id', $request->parent_id)->max('order') ?? 0;
            $request->merge(['order' => $maxOrder + 1]);
        }

        $menu = Menu::create($request->all());
        $menu->load(['parent', 'children', 'content']);

        return response()->json([
            'success' => true,
            'data' => $menu,
            'message' => 'Menu created successfully'
        ], 201);
    }

    /**
     * Display the specified menu.
     */
    public function show(Menu $menu): JsonResponse
    {
        $this->authorize('menus.view');

        $menu->load(['parent', 'children', 'content']);

        return response()->json([
            'success' => true,
            'data' => $menu,
            'message' => 'Menu retrieved successfully'
        ]);
    }

    /**
     * Update the specified menu.
     */
    public function update(Request $request, Menu $menu): JsonResponse
    {
        $this->authorize('menus.update');

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['list_menu', 'content_menu'])],
            'parent_id' => 'nullable|exists:idnbi_menus,id',
            'icon' => 'nullable|string|max:255',
            'route_or_url' => 'nullable|string|max:500',
            'content_id' => 'nullable|exists:idnbi_contents,id',
            'order' => 'integer|min:0',
            'role_permissions_required' => 'array',
            'role_permissions_required.*' => 'string',
        ]);

        // Prevent self-referencing
        if ($request->parent_id == $menu->id) {
            return response()->json([
                'success' => false,
                'message' => 'Menu cannot be its own parent'
            ], 422);
        }

        // Validate content_id for content_menu type
        if ($request->type === 'content_menu' && !$request->content_id) {
            return response()->json([
                'success' => false,
                'message' => 'Content ID is required for content menu type'
            ], 422);
        }

        $menu->update($request->all());
        $menu->load(['parent', 'children', 'content']);

        return response()->json([
            'success' => true,
            'data' => $menu,
            'message' => 'Menu updated successfully'
        ]);
    }

    /**
     * Remove the specified menu.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $this->authorize('menus.delete');

        // Check if menu has children
        if ($menu->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete menu with child items. Please delete or move child items first.'
            ], 422);
        }

        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu deleted successfully'
        ]);
    }

    /**
     * Reorder menus.
     */
    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('menus.reorder');

        $request->validate([
            'menus' => 'required|array',
            'menus.*.id' => 'required|exists:idnbi_menus,id',
            'menus.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->menus as $menuData) {
            Menu::where('id', $menuData['id'])->update(['order' => $menuData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu order updated successfully'
        ]);
    }

    /**
     * Get menu hierarchy for management.
     */
    public function hierarchy(): JsonResponse
    {
        $this->authorize('menus.view');

        $menus = Menu::with(['descendants.content'])
            ->roots()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $menus,
            'message' => 'Menu hierarchy retrieved successfully'
        ]);
    }

    /**
     * Get available content for menu linking.
     */
    public function availableContent(): JsonResponse
    {
        $this->authorize('menus.view');

        $contents = Content::select('id', 'title', 'slug', 'type')
            ->orderBy('title')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contents,
            'message' => 'Available content retrieved successfully'
        ]);
    }

    /**
     * Get children of a specific menu.
     */
    public function children(Menu $menu): JsonResponse
    {
        $this->authorize('menus.view');

        $children = $menu->children()
            ->with(['content'])
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $children,
            'message' => 'Menu children retrieved successfully'
        ]);
    }
}
