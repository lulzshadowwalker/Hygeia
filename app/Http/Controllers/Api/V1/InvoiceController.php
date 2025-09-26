<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\InvoiceResource;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        //  TODO: Invoice Authorization
        return InvoiceResource::collection(Invoice::all());
    }

    public function show(Invoice $invoice)
    {
        //  TODO: Invoice Authorization
        return InvoiceResource::make($invoice);
    }
}
