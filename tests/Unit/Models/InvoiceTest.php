<?php

namespace Tests\Unit;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_number_is_generated_and_unique()
    {
        $invoice1 = Invoice::create();

        $invoice2 = Invoice::create();

        $this->assertNotEmpty($invoice1->number);
        $this->assertNotEmpty($invoice2->number);
        $this->assertNotEquals($invoice1->number, $invoice2->number);
        $this->assertStringStartsWith('INV-', $invoice1->number);
        $this->assertStringStartsWith('INV-', $invoice2->number);
    }
}
