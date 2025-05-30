<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\TrackContentVisits;
use App\Models\ContentVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnalyticsTrackingController extends Controller
{
    /**
     * Update visit duration for content tracking.
     */
    public function updateVisitDuration(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'visit_id' => 'required|integer|exists:idnbi_content_visits,id',
                'duration_seconds' => 'required|integer|min:0|max:86400', // Max 24 hours
            ]);

            // Verify the visit belongs to the current user
            $visit = ContentVisit::where('id', $validated['visit_id'])
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$visit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Visit record not found or access denied.',
                ], 404);
            }

            $success = TrackContentVisits::updateVisitDuration(
                $validated['visit_id'],
                $validated['duration_seconds']
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Visit duration updated successfully.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update visit duration.',
            ], 500);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating visit duration.',
            ], 500);
        }
    }

    /**
     * Get popular content based on visit analytics.
     */
    public function getPopularContent(Request $request): JsonResponse
    {
        try {
            $limit = $request->query('limit', 10);
            $days = $request->query('days', 30);

            // Validate parameters
            if ($limit < 1 || $limit > 50) {
                $limit = 10;
            }
            if ($days < 1 || $days > 365) {
                $days = 30;
            }

            $popularContent = TrackContentVisits::getPopularContent($limit, $days);

            return response()->json([
                'success' => true,
                'data' => $popularContent,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve popular content.',
            ], 500);
        }
    }

    /**
     * Get popular menus based on visit analytics.
     */
    public function getPopularMenus(Request $request): JsonResponse
    {
        try {
            $limit = $request->query('limit', 10);
            $days = $request->query('days', 30);

            // Validate parameters
            if ($limit < 1 || $limit > 50) {
                $limit = 10;
            }
            if ($days < 1 || $days > 365) {
                $days = 30;
            }

            $popularMenus = TrackContentVisits::getPopularMenus($limit, $days);

            return response()->json([
                'success' => true,
                'data' => $popularMenus,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve popular menus.',
            ], 500);
        }
    }

    /**
     * Track custom event (for frontend analytics).
     */
    public function trackCustomEvent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_name' => 'required|string|max:100',
                'event_data' => 'nullable|array',
                'page_url' => 'nullable|url',
                'page_title' => 'nullable|string|max:255',
            ]);

            // For now, we'll use the content_visits table for custom events too
            // In the future, you might want a separate events table
            ContentVisit::create([
                'user_id' => $request->user()->id,
                'content_id' => null,
                'menu_id' => null,
                'page_type' => 'event',
                'page_title' => $validated['event_name'] . ': ' . ($validated['page_title'] ?? 'Custom Event'),
                'page_url' => $validated['page_url'] ?? $request->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'visited_at' => now(),
                'duration_seconds' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event tracked successfully.',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track event.',
            ], 500);
        }
    }
}
