<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StoreSupportTicketRequest;
use App\Http\Resources\V1\SupportTicketResource;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends ApiController
{
    public function index()
    {
        $this->authorize('viewAny', SupportTicket::class);

        return SupportTicketResource::collection(Auth::user()->supportTickets);
    }

    public function store(StoreSupportTicketRequest $request)
    {
        $ticket = SupportTicket::create($request->mappedAttributes([
            'user_id' => auth('sanctum')->user()?->id,
        ])->toArray());

        return SupportTicketResource::make($ticket);
    }

    public function show(SupportTicket $supportTicket)
    {
        $this->authorize('view', $supportTicket);

        return SupportTicketResource::make($supportTicket);
    }
}
