<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StoreSupportTicketRequest;
use App\Http\Resources\V1\SupportTicketResource;
use App\Models\SupportTicket;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Auth;

#[Group('Support')]
class SupportTicketController extends ApiController
{
    /**
     * List support tickets
     *
     * Get a list of all support tickets for the authenticated user.
     */
    public function index()
    {
        $this->authorize('viewAny', SupportTicket::class);

        return SupportTicketResource::collection(Auth::user()->supportTickets);
    }

    /**
     * Create a support ticket
     *
     * Create a new support ticket.
     */
    public function store(StoreSupportTicketRequest $request)
    {
        $ticket = SupportTicket::create($request->mappedAttributes([
            'user_id' => auth('sanctum')->user()?->id,
        ])->toArray());

        return SupportTicketResource::make($ticket);
    }

    /**
     * Get a support ticket
     *
     * Get the details of a specific support ticket.
     */
    public function show(SupportTicket $supportTicket)
    {
        $this->authorize('view', $supportTicket);

        return SupportTicketResource::make($supportTicket);
    }
}
