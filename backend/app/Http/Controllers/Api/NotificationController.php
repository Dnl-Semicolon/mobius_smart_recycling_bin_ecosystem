<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->appNotifications()
            ->latest()
            ->paginate(20)
            ->through(fn (AppNotification $n) => [
                'id' => $n->id,
                'type' => $n->type->value,
                'title' => $n->title,
                'body' => $n->body,
                'data' => $n->data,
                'is_read' => $n->read_at !== null,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        return response()->json($notifications);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $request->user()->appNotifications()->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request, AppNotification $appNotification): JsonResponse
    {
        abort_unless($appNotification->user_id === $request->user()->id, 403);

        $appNotification->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
