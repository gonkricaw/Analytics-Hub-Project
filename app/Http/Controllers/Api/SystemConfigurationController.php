<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SystemConfigurationController extends Controller
{
    /**
     * Display a listing of system configurations.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('admin.settings');

            $query = SystemConfiguration::query();

            // Filter by key if provided
            if ($request->has('key')) {
                $query->where('key', $request->key);
            }

            // Filter by type if provided
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter public/private configurations
            if ($request->has('is_public')) {
                $query->where('is_public', $request->boolean('is_public'));
            }

            $configurations = $query->orderBy('key')->get();

            return response()->json([
                'success' => true,
                'data' => $configurations,
                'message' => 'System configurations retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created system configuration.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('admin.settings');

            $validator = Validator::make($request->all(), [
                'key' => 'required|string|max:255|unique:idnbi_system_configurations,key',
                'value' => 'required',
                'type' => 'required|in:string,number,boolean,json,file',
                'description' => 'nullable|string|max:500',
                'is_public' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Handle file upload if type is file
            if ($data['type'] === 'file' && $request->hasFile('value')) {
                $file = $request->file('value');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('system-configurations', $filename, 'public');
                $data['value'] = $path;
            }

            // Handle JSON type
            if ($data['type'] === 'json' && is_string($data['value'])) {
                $jsonValue = json_decode($data['value'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid JSON format'
                    ], 422);
                }
                $data['value'] = $jsonValue;
            }

            $configuration = SystemConfiguration::create($data);

            return response()->json([
                'success' => true,
                'data' => $configuration,
                'message' => 'System configuration created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create system configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified system configuration.
     */
    public function show(string $key): JsonResponse
    {
        try {
            $this->authorize('admin.settings');

            $configuration = SystemConfiguration::where('key', $key)->first();

            if (!$configuration) {
                return response()->json([
                    'success' => false,
                    'message' => 'System configuration not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $configuration,
                'message' => 'System configuration retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified system configuration.
     */
    public function update(Request $request, string $key): JsonResponse
    {
        try {
            $this->authorize('admin.settings');

            $configuration = SystemConfiguration::where('key', $key)->first();

            if (!$configuration) {
                return response()->json([
                    'success' => false,
                    'message' => 'System configuration not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'value' => 'required',
                'type' => 'sometimes|in:string,number,boolean,json,file',
                'description' => 'nullable|string|max:500',
                'is_public' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Handle file upload if type is file
            if (($data['type'] ?? $configuration->type) === 'file' && $request->hasFile('value')) {
                // Delete old file if exists
                if ($configuration->type === 'file' && $configuration->value && Storage::disk('public')->exists($configuration->value)) {
                    Storage::disk('public')->delete($configuration->value);
                }

                $file = $request->file('value');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('system-configurations', $filename, 'public');
                $data['value'] = $path;
            }

            // Handle JSON type
            if (($data['type'] ?? $configuration->type) === 'json' && is_string($data['value'])) {
                $jsonValue = json_decode($data['value'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid JSON format'
                    ], 422);
                }
                $data['value'] = $jsonValue;
            }

            $configuration->update($data);

            return response()->json([
                'success' => true,
                'data' => $configuration->fresh(),
                'message' => 'System configuration updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update system configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified system configuration.
     */
    public function destroy(string $key): JsonResponse
    {
        try {
            $this->authorize('admin.settings');

            $configuration = SystemConfiguration::where('key', $key)->first();

            if (!$configuration) {
                return response()->json([
                    'success' => false,
                    'message' => 'System configuration not found'
                ], 404);
            }

            // Delete associated file if exists
            if ($configuration->type === 'file' && $configuration->value && Storage::disk('public')->exists($configuration->value)) {
                Storage::disk('public')->delete($configuration->value);
            }

            $configuration->delete();

            return response()->json([
                'success' => true,
                'message' => 'System configuration deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete system configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public system configurations (accessible without authentication).
     */
    public function public(): JsonResponse
    {
        try {
            $configurations = SystemConfiguration::where('is_public', true)
                ->get()
                ->keyBy('key')
                ->map(function ($config) {
                    return $config->getValue();
                });

            return response()->json([
                'success' => true,
                'data' => $configurations,
                'message' => 'Public system configurations retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve public system configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update multiple system configurations.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            $this->authorize('admin.settings');

            $validator = Validator::make($request->all(), [
                'configurations' => 'required|array',
                'configurations.*.key' => 'required|string|exists:idnbi_system_configurations,key',
                'configurations.*.value' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updatedConfigurations = [];

            foreach ($request->configurations as $configData) {
                $configuration = SystemConfiguration::where('key', $configData['key'])->first();
                
                if ($configuration) {
                    $value = $configData['value'];

                    // Handle JSON type
                    if ($configuration->type === 'json' && is_string($value)) {
                        $jsonValue = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $value = $jsonValue;
                        }
                    }

                    $configuration->update(['value' => $value]);
                    $updatedConfigurations[] = $configuration->fresh();
                }
            }

            return response()->json([
                'success' => true,
                'data' => $updatedConfigurations,
                'message' => 'System configurations updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update system configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get configurations grouped by category.
     */
    public function grouped(): JsonResponse
    {
        try {
            $this->authorize('admin.settings');

            $configurations = SystemConfiguration::all();
            
            $grouped = $configurations->groupBy(function ($config) {
                $keyParts = explode('_', $config->key);
                return $keyParts[0] ?? 'general';
            });

            return response()->json([
                'success' => true,
                'data' => $grouped,
                'message' => 'Grouped system configurations retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve grouped system configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
