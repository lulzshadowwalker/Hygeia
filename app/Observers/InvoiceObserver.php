<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        do {
            $number = 'INV-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while (Invoice::where('number', $number)->exists());

        $invoice->number = $number;
    }
}
