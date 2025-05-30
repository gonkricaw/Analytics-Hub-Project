<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class EmailTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of email templates.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('email-templates.view');

        $query = EmailTemplate::with('createdBy:id,name');

        // Search by name or description
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'ILIKE', '%' . $searchTerm . '%');
            });
        }

        // Filter by type
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by creator
        if ($request->has('created_by') && !empty($request->created_by)) {
            $query->where('created_by_user_id', $request->created_by);
        }

        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['name', 'type', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $templates = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $templates,
            'message' => 'Email templates retrieved successfully'
        ]);
    }

    /**
     * Store a newly created email template.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('email-templates.create');

        $request->validate([
            'name' => 'required|string|max:255|unique:idnbi_email_templates,name',
            'type' => ['required', Rule::in(array_keys(EmailTemplate::getAvailableTypes()))],
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'placeholders' => 'nullable|array',
            'placeholders.*' => 'string|max:100',
            'is_active' => 'boolean',
        ]);

        // Validate that all placeholders in content are declared
        $htmlPlaceholders = EmailTemplate::extractPlaceholders($request->html_content);
        $textPlaceholders = EmailTemplate::extractPlaceholders($request->text_content ?? '');
        $subjectPlaceholders = EmailTemplate::extractPlaceholders($request->subject);
        
        $allContentPlaceholders = array_unique(array_merge($htmlPlaceholders, $textPlaceholders, $subjectPlaceholders));
        $declaredPlaceholders = $request->placeholders ?? [];

        $undeclaredPlaceholders = array_diff($allContentPlaceholders, $declaredPlaceholders);
        if (!empty($undeclaredPlaceholders)) {
            return response()->json([
                'success' => false,
                'message' => 'The following placeholders are used in content but not declared: ' . implode(', ', $undeclaredPlaceholders),
                'errors' => [
                    'placeholders' => ['Please declare all placeholders used in content']
                ]
            ], 422);
        }

        $template = EmailTemplate::create([
            'name' => $request->name,
            'type' => $request->type,
            'subject' => $request->subject,
            'html_content' => $request->html_content,
            'text_content' => $request->text_content,
            'description' => $request->description,
            'placeholders' => $request->placeholders,
            'is_active' => $request->boolean('is_active', true),
            'created_by_user_id' => $request->user()->id,
        ]);

        $template->load('createdBy:id,name');

        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Email template created successfully'
        ], 201);
    }

    /**
     * Display the specified email template.
     */
    public function show(EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorize('email-templates.view');

        $emailTemplate->load('createdBy:id,name');

        // Extract placeholders from content for reference
        $htmlPlaceholders = EmailTemplate::extractPlaceholders($emailTemplate->html_content);
        $textPlaceholders = EmailTemplate::extractPlaceholders($emailTemplate->text_content ?? '');
        $subjectPlaceholders = EmailTemplate::extractPlaceholders($emailTemplate->subject);
        
        $contentPlaceholders = array_unique(array_merge($htmlPlaceholders, $textPlaceholders, $subjectPlaceholders));

        return response()->json([
            'success' => true,
            'data' => [
                'template' => $emailTemplate,
                'content_placeholders' => $contentPlaceholders,
                'available_types' => EmailTemplate::getAvailableTypes(),
            ],
            'message' => 'Email template retrieved successfully'
        ]);
    }

    /**
     * Update the specified email template.
     */
    public function update(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorize('email-templates.update');

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:idnbi_email_templates,name,' . $emailTemplate->id,
            'type' => ['sometimes', 'required', Rule::in(array_keys(EmailTemplate::getAvailableTypes()))],
            'subject' => 'sometimes|required|string|max:255',
            'html_content' => 'sometimes|required|string',
            'text_content' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'placeholders' => 'nullable|array',
            'placeholders.*' => 'string|max:100',
            'is_active' => 'boolean',
        ]);

        // Validate placeholders only if content fields are being updated
        $htmlContent = $request->has('html_content') ? $request->html_content : $emailTemplate->html_content;
        $textContent = $request->has('text_content') ? $request->text_content : $emailTemplate->text_content;
        $subject = $request->has('subject') ? $request->subject : $emailTemplate->subject;
        
        $htmlPlaceholders = EmailTemplate::extractPlaceholders($htmlContent);
        $textPlaceholders = EmailTemplate::extractPlaceholders($textContent ?? '');
        $subjectPlaceholders = EmailTemplate::extractPlaceholders($subject);
        
        $allContentPlaceholders = array_unique(array_merge($htmlPlaceholders, $textPlaceholders, $subjectPlaceholders));
        $declaredPlaceholders = $request->has('placeholders') ? $request->placeholders : ($emailTemplate->placeholders ?? []);

        $undeclaredPlaceholders = array_diff($allContentPlaceholders, $declaredPlaceholders);
        if (!empty($undeclaredPlaceholders)) {
            return response()->json([
                'success' => false,
                'message' => 'The following placeholders are used in content but not declared: ' . implode(', ', $undeclaredPlaceholders),
                'errors' => [
                    'placeholders' => ['Please declare all placeholders used in content']
                ]
            ], 422);
        }

        // Prepare update data with only provided fields
        $updateData = [];
        if ($request->has('name')) $updateData['name'] = $request->name;
        if ($request->has('type')) $updateData['type'] = $request->type;
        if ($request->has('subject')) $updateData['subject'] = $request->subject;
        if ($request->has('html_content')) $updateData['html_content'] = $request->html_content;
        if ($request->has('text_content')) $updateData['text_content'] = $request->text_content;
        if ($request->has('description')) $updateData['description'] = $request->description;
        if ($request->has('placeholders')) $updateData['placeholders'] = $request->placeholders;
        if ($request->has('is_active')) $updateData['is_active'] = $request->boolean('is_active');

        $emailTemplate->update($updateData);

        $emailTemplate->load('createdBy:id,name');

        return response()->json([
            'success' => true,
            'data' => $emailTemplate,
            'message' => 'Email template updated successfully'
        ]);
    }

    /**
     * Remove the specified email template.
     */
    public function destroy(EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorize('email-templates.delete');

        // Check if this is a default template
        if ($emailTemplate->type && $emailTemplate->name === 'Default ' . ucfirst($emailTemplate->type) . ' Template') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete default templates. You can deactivate them instead.',
            ], 422);
        }

        $emailTemplate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email template deleted successfully'
        ], 204);
    }

    /**
     * Get template types.
     */
    public function types(): JsonResponse
    {
        $this->authorize('email-templates.view');

        $types = EmailTemplate::getAvailableTypes();

        return response()->json($types);
    }

    /**
     * Preview template with sample data.
     */
    public function preview(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorize('email-templates.view');

        $request->validate([
            'data' => 'array',
        ]);

        $sampleData = $request->get('data', []);

        // Add default sample data for missing placeholders
        $defaultSampleData = [
            'user_name' => 'John Doe',
            'user_email' => 'john.doe@example.com',
            'app_name' => config('app.name', 'Indonet Analytics Hub'),
            'app_url' => config('app.url'),
            'company_name' => 'PT Indonet',
            'current_date' => now()->format('F j, Y'),
            'current_year' => now()->format('Y'),
        ];

        $mergedData = array_merge($defaultSampleData, $sampleData);

        try {
            $compiledSubject = $emailTemplate->compileTemplate($emailTemplate->subject, $mergedData);
            $compiledHtml = $emailTemplate->compileTemplate($emailTemplate->html_content, $mergedData);
            $compiledText = $emailTemplate->text_content 
                ? $emailTemplate->compileTemplate($emailTemplate->text_content, $mergedData)
                : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'subject' => $compiledSubject,
                    'html_content' => $compiledHtml,
                    'text_content' => $compiledText,
                    'used_data' => $mergedData,
                ],
                'message' => 'Template preview generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating preview: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Clone an existing template.
     */
    public function clone(EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorize('email-templates.create');

        $clonedTemplate = EmailTemplate::create([
            'name' => $emailTemplate->name . ' (Copy)',
            'type' => $emailTemplate->type,
            'subject' => $emailTemplate->subject,
            'html_content' => $emailTemplate->html_content,
            'text_content' => $emailTemplate->text_content,
            'description' => $emailTemplate->description . ' (Cloned from original)',
            'placeholders' => $emailTemplate->placeholders,
            'is_active' => false, // Clone as inactive by default
            'created_by_user_id' => auth()->id(),
        ]);

        $clonedTemplate->load('createdBy:id,name');

        return response()->json([
            'success' => true,
            'data' => $clonedTemplate,
            'message' => 'Email template cloned successfully'
        ], 201);
    }

    /**
     * Toggle template active status.
     */
    public function toggleStatus(EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorize('email-templates.update');

        $emailTemplate->update([
            'is_active' => !$emailTemplate->is_active
        ]);

        return response()->json([
            'success' => true,
            'data' => $emailTemplate,
            'message' => 'Template status updated successfully'
        ]);
    }

    /**
     * Create default templates for all types.
     */
    public function createDefaults(): JsonResponse
    {
        $this->authorize('email-templates.create');

        $created = [];
        $types = EmailTemplate::getAvailableTypes();

        foreach ($types as $type) {
            $defaultTemplate = EmailTemplate::createDefaultTemplate($type, auth()->id());
            if ($defaultTemplate) {
                $created[] = $defaultTemplate;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $created,
            'message' => count($created) . ' default templates created successfully'
        ], 201);
    }
}
