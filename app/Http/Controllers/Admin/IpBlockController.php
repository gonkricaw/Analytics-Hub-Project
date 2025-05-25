<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpBlock;
use App\Models\FailedLoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IpBlockController extends Controller
{
    /**
     * Get all IP blocks
     */
    public function index(Request $request): JsonResponse
    {
        $query = IpBlock::with('unblockedBy:id,name');

        // Filter by status if provided
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $ipBlocks = $query->orderBy('blocked_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $ipBlocks,
        ]);
    }

    /**
     * Get failed login attempts for an IP address
     */
    public function failedAttempts(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'hours' => 'nullable|integer|min:1|max:168', // Max 1 week
        ]);

        $hours = $request->hours ?? 24;
        $ipAddress = $request->ip_address;

        $attempts = FailedLoginAttempt::byIpAddress($ipAddress)
            ->where('created_at', '>=', now()->subHours($hours))
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $attempts,
            'meta' => [
                'ip_address' => $ipAddress,
                'hours_range' => $hours,
                'is_currently_blocked' => IpBlock::isBlocked($ipAddress),
            ],
        ]);
    }

    /**
     * Manually block an IP address
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255',
        ]);

        $ipAddress = $request->ip_address;

        // Check if IP is already blocked
        if (IpBlock::isBlocked($ipAddress)) {
            return response()->json([
                'success' => false,
                'message' => 'IP address is already blocked.',
            ], 400);
        }

        $ipBlock = IpBlock::blockIp($ipAddress, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'IP address blocked successfully.',
            'data' => $ipBlock,
        ], 201);
    }

    /**
     * Unblock an IP address
     */
    public function unblock(Request $request, IpBlock $ipBlock): JsonResponse
    {
        if (!$ipBlock->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'IP address is not currently blocked.',
            ], 400);
        }

        $ipBlock->unblock($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'IP address unblocked successfully.',
            'data' => $ipBlock->fresh()->load('unblockedBy:id,name'),
        ]);
    }

    /**
     * Show specific IP block details
     */
    public function show(IpBlock $ipBlock): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $ipBlock->load('unblockedBy:id,name'),
        ]);
    }

    /**
     * Get IP block statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_blocks' => IpBlock::count(),
            'active_blocks' => IpBlock::active()->count(),
            'blocks_today' => IpBlock::whereDate('blocked_at', today())->count(),
            'blocks_this_week' => IpBlock::where('blocked_at', '>=', now()->startOfWeek())->count(),
            'unblocks_today' => IpBlock::whereDate('unblocked_at', today())->count(),
            'failed_attempts_today' => FailedLoginAttempt::whereDate('created_at', today())->count(),
            'failed_attempts_this_hour' => FailedLoginAttempt::where('created_at', '>=', now()->subHour())->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Bulk unblock multiple IP addresses
     */
    public function bulkUnblock(Request $request): JsonResponse
    {
        $request->validate([
            'ip_block_ids' => 'required|array',
            'ip_block_ids.*' => 'exists:idnbi_ip_blocks,id',
        ]);

        $ipBlockIds = $request->ip_block_ids;
        $unblocked = 0;

        foreach ($ipBlockIds as $id) {
            $ipBlock = IpBlock::find($id);
            if ($ipBlock && $ipBlock->is_active) {
                $ipBlock->unblock($request->user()->id);
                $unblocked++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully unblocked {$unblocked} IP address(es).",
            'data' => [
                'unblocked_count' => $unblocked,
                'requested_count' => count($ipBlockIds),
            ],
        ]);
    }
}
