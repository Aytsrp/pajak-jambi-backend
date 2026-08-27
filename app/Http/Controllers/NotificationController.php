<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    #[OA\Get(
        path: "/api/notifications",
        summary: "List notifikasi milik user, terbaru duluan",
        tags: ["Notifications"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "unread_only", in: "query", schema: new OA\Schema(type: "boolean")),
        ],
        responses: [new OA\Response(response: 200, description: "Daftar notifikasi")]
    )]
    public function index(Request $request)
    {
        $query = $request->user()->notifications()->latest('sent_at');

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        return NotificationResource::collection($query->paginate(20));
    }

    #[OA\Get(
        path: "/api/notifications/unread-count",
        summary: "Jumlah notifikasi belum dibaca (untuk badge di Flutter)",
        tags: ["Notifications"],
        security: [["bearerAuth" => []]],
        responses: [new OA\Response(response: 200, description: "Jumlah unread")]
    )]
    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $request->user()->notifications()->where('is_read', false)->count(),
        ]);
    }

    #[OA\Post(
        path: "/api/notifications/{id}/read",
        summary: "Tandai 1 notifikasi sudah dibaca",
        tags: ["Notifications"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Berhasil ditandai dibaca"),
            new OA\Response(response: 404, description: "Tidak ditemukan / bukan milik user ini"),
        ]
    )]
    public function markAsRead(Request $request, int $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);

        return NotificationResource::make($notification);
    }

    #[OA\Post(
        path: "/api/notifications/read-all",
        summary: "Tandai semua notifikasi sudah dibaca",
        tags: ["Notifications"],
        security: [["bearerAuth" => []]],
        responses: [new OA\Response(response: 200, description: "Semua ditandai dibaca")]
    )]
    public function markAllAsRead(Request $request)
    {
        $count = $request->user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['message' => "{$count} notifikasi ditandai dibaca."]);
    }
}