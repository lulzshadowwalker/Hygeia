<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\InvoiceResource;
use App\Models\Invoice;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

#[Group('Invoices')]
class InvoiceController extends ApiController
{
    /**
     * List invoices
     *
     * Get a list of all invoices for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Invoice::class);

        return InvoiceResource::collection(
            Invoice::query()
                ->whereBelongsTo($request->user())
                ->get()
        );
    }

    /**
     * Get an invoice
     *
     * Get the details of a specific invoice.
     */
    public function show(Invoice $invoice): JsonResource
    {
        $this->authorize('view', $invoice);

        return InvoiceResource::make($invoice);
    }
}
