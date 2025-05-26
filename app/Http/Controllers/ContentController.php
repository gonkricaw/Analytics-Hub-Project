<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContentController extends Controller
{
    /**
     * Display a listing of accessible content.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Content::with(['creator', 'updater']);
        
        // Filter by type if specified
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        $contents = $query->get()->filter(function($content) use ($user) {
            return $content->isAccessibleBy($user);
        });

        return response()->json([
            'success' => true,
            'data' => $contents->values(),
            'message' => 'Content retrieved successfully'
        ]);
    }

    /**
     * Display the specified content.
     */
    public function show(Request $request, Content $content): JsonResponse
    {
        $user = $request->user();

        if (!$content->isAccessibleBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $content->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'data' => $content,
            'message' => 'Content retrieved successfully'
        ]);
    }

    /**
     * Get content by slug.
     */
    public function showBySlug(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        
        $content = Content::where('slug', $slug)
            ->with(['creator', 'updater'])
            ->firstOrFail();

        if (!$content->isAccessibleBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $content,
            'message' => 'Content retrieved successfully'
        ]);
    }

    /**
     * Serve embedded content by UUID with decrypted URL.
     */
    public function embed(Request $request, string $uuid): Response
    {
        $user = $request->user();
        
        $content = Content::where('embed_url_uuid', $uuid)
            ->firstOrFail();

        if (!$content->isAccessibleBy($user)) {
            abort(403, 'Access denied');
        }

        if (!$content->embed_url_original) {
            abort(404, 'Embed URL not found');
        }

        // Decrypt and serve the original URL
        $originalUrl = $content->getDecryptedEmbedUrl();
        
        if (!$originalUrl) {
            abort(500, 'Unable to decrypt embed URL');
        }

        // Return a view that embeds the decrypted URL
        return response()->view('embed.content', [
            'url' => $originalUrl,
            'content' => $content,
            'user' => $user
        ]);
    }
}
