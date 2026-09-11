<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    #[OA\Get(
        path: "/api/notifications",
        summary: "List notifikasi milik user, terbaru duluan (paginasi)",
        tags: ["Notifications"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "unread_only", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "page", in: "query", description: "Nomor halaman, mulai dari 1", schema: new OA\Schema(type: "integer", default: 1, minimum: 1)),
            new OA\Parameter(name: "per_page", in: "query", description: "Jumlah item per halaman (maks. 50)", schema: new OA\Schema(type: "integer", default: 20, minimum: 1, maximum: 50)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Daftar notifikasi. Flutter wajib baca meta.current_page / meta.last_page (atau links.next) untuk infinite scroll.",
            ),
        ]
    )]
    public function index(Request $request)
    {
        $request->validate([
            'unread_only' => ['sometimes', 'boolean'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $query = $request->user()->notifications()->latest('sent_at');

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        $perPage = $request->integer('per_page', 20);

        return NotificationResource::collection($query->paginate($perPage));
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