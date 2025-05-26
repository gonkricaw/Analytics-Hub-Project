<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of all content for management.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('content.view');

        $query = Content::with(['creator', 'updater']);

        // Filter by type if specified
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Search by title if specified
        if ($request->has('search')) {
            $query->where('title', 'ILIKE', '%' . $request->search . '%');
        }

        $contents = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $contents,
            'message' => 'Content retrieved successfully'
        ]);
    }

    /**
     * Store a newly created content.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('content.create');

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:idnbi_contents,slug',
            'type' => ['required', Rule::in(['custom', 'embed_url'])],
            'custom_content' => 'required_if:type,custom|nullable|string',
            'embed_url_original' => 'required_if:type,embed_url|nullable|url',
        ]);

        $data = $request->all();
        $data['created_by_user_id'] = $request->user()->id;
        $data['updated_by_user_id'] = $request->user()->id;

        // Auto-generate slug if not provided
        if (!$data['slug']) {
            $data['slug'] = Str::slug($data['title']);
            
            // Ensure slug uniqueness
            $baseSlug = $data['slug'];
            $counter = 1;
            while (Content::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        $content = Content::create($data);
        $content->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'data' => $content,
            'message' => 'Content created successfully'
        ], 201);
    }

    /**
     * Display the specified content.
     */
    public function show(Content $content): JsonResponse
    {
        $this->authorize('content.view');

        $content->load(['creator', 'updater']);

        // Include decrypted URL for embed type (admin only)
        if ($content->type === 'embed_url') {
            $content->decrypted_embed_url = $content->getDecryptedEmbedUrl();
        }

        return response()->json([
            'success' => true,
            'data' => $content,
            'message' => 'Content retrieved successfully'
        ]);
    }

    /**
     * Update the specified content.
     */
    public function update(Request $request, Content $content): JsonResponse
    {
        $this->authorize('content.update');

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:idnbi_contents,slug,' . $content->id,
            'type' => ['required', Rule::in(['custom', 'embed_url'])],
            'custom_content' => 'required_if:type,custom|nullable|string',
            'embed_url_original' => 'required_if:type,embed_url|nullable|url',
        ]);

        $data = $request->all();
        $data['updated_by_user_id'] = $request->user()->id;

        // Handle slug generation if title changed
        if ($request->title !== $content->title && !$request->slug) {
            $data['slug'] = Str::slug($request->title);
            
            // Ensure slug uniqueness
            $baseSlug = $data['slug'];
            $counter = 1;
            while (Content::where('slug', $data['slug'])->where('id', '!=', $content->id)->exists()) {
                $data['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        $content->update($data);
        $content->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'data' => $content,
            'message' => 'Content updated successfully'
        ]);
    }

    /**
     * Remove the specified content.
     */
    public function destroy(Content $content): JsonResponse
    {
        $this->authorize('content.delete');

        // Check if content is linked to any menus
        if ($content->menus()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete content that is linked to menu items. Please unlink from menus first.',
                'linked_menus_count' => $content->menus()->count()
            ], 422);
        }

        $content->delete();

        return response()->json([
            'success' => true,
            'message' => 'Content deleted successfully'
        ]);
    }

    /**
     * Get content statistics.
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('content.view');

        $stats = [
            'total_content' => Content::count(),
            'custom_content' => Content::where('type', 'custom')->count(),
            'embed_content' => Content::where('type', 'embed_url')->count(),
            'recent_content' => Content::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Content statistics retrieved successfully'
        ]);
    }

    /**
     * Get content by slug or UUID for preview.
     */
    public function preview(string $identifier): JsonResponse
    {
        $this->authorize('content.view');

        $content = Content::where('slug', $identifier)
            ->orWhere('embed_url_uuid', $identifier)
            ->with(['creator', 'updater'])
            ->firstOrFail();

        // Include decrypted URL for embed type (admin only)
        if ($content->type === 'embed_url') {
            $content->decrypted_embed_url = $content->getDecryptedEmbedUrl();
        }

        return response()->json([
            'success' => true,
            'data' => $content,
            'message' => 'Content preview retrieved successfully'
        ]);
    }

    /**
     * Duplicate content.
     */
    public function duplicate(Content $content): JsonResponse
    {
        $this->authorize('content.create');

        $newContent = $content->replicate();
        $newContent->title = $content->title . ' (Copy)';
        $newContent->slug = Str::slug($newContent->title);
        
        // Ensure slug uniqueness
        $baseSlug = $newContent->slug;
        $counter = 1;
        while (Content::where('slug', $newContent->slug)->exists()) {
            $newContent->slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $newContent->created_by_user_id = request()->user()->id;
        $newContent->updated_by_user_id = request()->user()->id;
        
        // Generate new UUID for embed type
        if ($content->type === 'embed_url') {
            $newContent->embed_url_uuid = Str::uuid();
        }

        $newContent->save();
        $newContent->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'data' => $newContent,
            'message' => 'Content duplicated successfully'
        ], 201);
    }
}
