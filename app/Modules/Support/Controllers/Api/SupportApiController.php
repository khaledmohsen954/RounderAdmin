<?php

namespace App\Modules\Support\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Support\Requests\ApiCreateSupportTicketRequest;
use App\Modules\Support\Services\SupportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportApiController extends Controller
{
    public function __construct(
        private readonly SupportService $supportService
    ) {
    }

    /**
     * جلب قنوات التواصل والتصنيفات المتاحة لشاشة "تواصل معنا".
     */
    public function info(): JsonResponse
    {
        $info = $this->supportService->getSupportInfo();

        return response()->json([
            'status' => true,
            'data' => $info,
        ]);
    }

    /**
     * إرسال رسالة/تذكرة دعم جديدة من قِبل الساكن.
     */
    public function store(ApiCreateSupportTicketRequest $request): JsonResponse
    {
        $user = $request->user();
        $attachment = $request->file('attachment');
        $ticket = $this->supportService->createTicket($user, $request->validated(), $attachment);

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال رسالتك بنجاح، وسيقوم فريق الدعم بالرد عليك قريباً.',
            'data' => [
                'id' => $ticket->id,
                'reference' => $ticket->reference,
                'category' => $ticket->category,
                'category_name' => $ticket->category_name,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'attachment_url' => $ticket->attachment_url,
                'status' => $ticket->status,
                'status_name' => $ticket->status_name,
                'created_at' => $ticket->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * قائمة رسائل وتذاكر الدعم الخاصة بالساكن الحالي.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = $this->supportService->residentTickets($request->user());

        $items = collect($tickets->items())->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'reference' => $ticket->reference,
                'category' => $ticket->category,
                'category_name' => $ticket->category_name,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'attachment_url' => $ticket->attachment_url,
                'status' => $ticket->status,
                'status_name' => $ticket->status_name,
                'admin_reply' => $ticket->admin_reply,
                'replied_at' => $ticket->replied_at?->toIso8601String(),
                'created_at' => $ticket->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * تفاصيل تذكرة دعم محددة للساكن.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = $this->supportService->findForResident($request->user(), $id);

        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'التذكرة غير موجودة.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $ticket->id,
                'reference' => $ticket->reference,
                'category' => $ticket->category,
                'category_name' => $ticket->category_name,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'attachment_url' => $ticket->attachment_url,
                'status' => $ticket->status,
                'status_name' => $ticket->status_name,
                'admin_reply' => $ticket->admin_reply,
                'replied_at' => $ticket->replied_at?->toIso8601String(),
                'created_at' => $ticket->created_at?->toIso8601String(),
            ],
        ]);
    }
}

