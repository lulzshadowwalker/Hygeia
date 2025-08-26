<?php

namespace App\Listeners;

use App\Events\PaymentPaid;
use App\Models\Invoice;
use Spatie\Browsershot\Browsershot;

class SendInvoice
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentPaid $event): void
    {
        DB::transaction(function () use ($event) {
            // $payment = $event->payment;

            // $invoice = $payment->invoice()->create();

            $invoice = Invoice::create();

            $html = view('invoices.show', compact('invoice'))->render();

            if (! app()->environment('testing')) {
                Browsershot::html($html)
                    ->showBackground()
                    ->save($invoice->filepath());
            }

            //  TODO: Send out the notification to the client when making an invoice
            // $payment->payable->payer()->notify(new InvoicePaid($invoice));
        });
    }
}
