<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\InvoiceResource;
use App\Models\Invoice;
use Dedoc\Scramble\Attributes\Group;

#[Group('Invoices')]
class InvoiceController extends Controller
{
    /**
     * List invoices
     *
     * Get a list of all invoices.
     */
    public function index()
    {
        //  TODO: Invoice Authorization
        return InvoiceResource::collection(Invoice::all());
    }

    /**
     * Get an invoice
     *
     * Get the details of a specific invoice.
     */
    public function show(Invoice $invoice)
    {
        //  TODO: Invoice Authorization
        return InvoiceResource::make($invoice);
    }
}
