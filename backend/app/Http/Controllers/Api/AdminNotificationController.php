<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class AdminNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'unread_only' => ['sometimes', 'boolean'],
        ]);

        $query = $request->boolean('unread_only')
            ? $request->user()->unreadNotifications()
            : $request->user()->notifications();

        $notifications = $query
            ->latest('created_at')
            ->paginate($data['per_page'] ?? 15);

        return response()->json([
            'data' => collect($notifications->items())
                ->map(fn (DatabaseNotification $notification) => $this->serialize($notification))
                ->values(),
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        /** @var DatabaseNotification $notification */
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();
        $notification->refresh();

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $this->serialize($notification),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $updated = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'updated_count' => $updated,
            'unread_count' => 0,
        ]);
    }

    private function serialize(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'type' => $data['kind'] ?? 'notification',
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? '',
            'action_url' => $data['action_url'] ?? null,
            'data' => $data,
            'is_read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
        ];
    }
}
