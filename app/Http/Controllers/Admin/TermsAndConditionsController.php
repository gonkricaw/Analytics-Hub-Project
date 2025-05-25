<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TermsAndConditions;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TermsAndConditionsController extends Controller
{
    /**
     * Get all terms and conditions
     */
    public function index(): JsonResponse
    {
        $terms = TermsAndConditions::with('createdBy:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $terms,
        ]);
    }

    /**
     * Get current active terms and conditions
     */
    public function current(): JsonResponse
    {
        $currentTerms = TermsAndConditions::getCurrent();

        if (!$currentTerms) {
            return response()->json([
                'success' => false,
                'message' => 'No active terms and conditions found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $currentTerms,
        ]);
    }

    /**
     * Store new terms and conditions
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'version' => 'nullable|string|max:50',
        ]);

        $version = $request->version ?: TermsAndConditions::getLatestVersion();

        $terms = TermsAndConditions::create([
            'content' => $request->content,
            'version' => $version,
            'created_by' => $request->user()->id,
            'is_active' => false, // Start as inactive
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions created successfully.',
            'data' => $terms->load('createdBy:id,name'),
        ], 201);
    }

    /**
     * Show specific terms and conditions
     */
    public function show(TermsAndConditions $termsAndCondition): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $termsAndCondition->load('createdBy:id,name'),
        ]);
    }

    /**
     * Update terms and conditions
     */
    public function update(Request $request, TermsAndConditions $termsAndCondition): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'version' => 'required|string|max:50',
        ]);

        // Don't allow editing active terms
        if ($termsAndCondition->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit active terms and conditions. Create a new version instead.',
            ], 400);
        }

        $termsAndCondition->update([
            'content' => $request->content,
            'version' => $request->version,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions updated successfully.',
            'data' => $termsAndCondition->load('createdBy:id,name'),
        ]);
    }

    /**
     * Activate terms and conditions
     */
    public function activate(TermsAndConditions $termsAndCondition): JsonResponse
    {
        $termsAndCondition->activate();

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions activated successfully.',
            'data' => $termsAndCondition->fresh()->load('createdBy:id,name'),
        ]);
    }

    /**
     * Deactivate terms and conditions
     */
    public function deactivate(TermsAndConditions $termsAndCondition): JsonResponse
    {
        $termsAndCondition->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions deactivated successfully.',
            'data' => $termsAndCondition->fresh()->load('createdBy:id,name'),
        ]);
    }

    /**
     * Delete terms and conditions
     */
    public function destroy(TermsAndConditions $termsAndCondition): JsonResponse
    {
        // Don't allow deleting active terms
        if ($termsAndCondition->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete active terms and conditions.',
            ], 400);
        }

        $termsAndCondition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions deleted successfully.',
        ]);
    }
}
