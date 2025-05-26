<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of accessible menus for the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Get root menu items with their children
        $menus = Menu::with(['children' => function($query) use ($user) {
                $query->ordered();
            }, 'content'])
            ->roots()
            ->ordered()
            ->get()
            ->filter(function($menu) use ($user) {
                return $menu->isAccessibleBy($user);
            });

        // Filter children based on user permissions
        $menus->each(function($menu) use ($user) {
            if ($menu->children) {
                $menu->children = $menu->children->filter(function($child) use ($user) {
                    return $child->isAccessibleBy($user);
                });
            }
        });

        return response()->json([
            'success' => true,
            'data' => $menus->values(),
            'message' => 'Menus retrieved successfully'
        ]);
    }

    /**
     * Get menu hierarchy for navigation.
     */
    public function hierarchy(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $menus = Menu::with(['descendants.content'])
            ->roots()
            ->ordered()
            ->get()
            ->filter(function($menu) use ($user) {
                return $menu->isAccessibleBy($user);
            });

        // Recursively filter accessible menus
        $filterAccessible = function($items) use ($user, &$filterAccessible) {
            return $items->filter(function($item) use ($user) {
                return $item->isAccessibleBy($user);
            })->map(function($item) use ($filterAccessible) {
                if ($item->descendants) {
                    $item->descendants = $filterAccessible($item->descendants);
                }
                return $item;
            });
        };

        $filteredMenus = $filterAccessible($menus);

        return response()->json([
            'success' => true,
            'data' => $filteredMenus->values(),
            'message' => 'Menu hierarchy retrieved successfully'
        ]);
    }
}
