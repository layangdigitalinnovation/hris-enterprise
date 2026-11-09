<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ApprovalNotificationService;

class NotificationController extends Controller
{
    protected ApprovalNotificationService $notificationService;

    public function __construct(ApprovalNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get real-time pending approval notifications for current user.
     */
    public function approvals(Request $request): JsonResponse
    {
        $user = $request->user();
        $summary = $this->notificationService->getSummary($user);

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}

