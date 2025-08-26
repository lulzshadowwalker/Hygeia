<?php

namespace App\Models;

use App\Observers\InvoiceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(InvoiceObserver::class)]
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    protected $fillable = [];

    public function filepath(): string
    {
        return storage_path("app/invoices/{$this->number}.pdf");
    }

    // TODO: Implement private files

    public function strippedNumber(): Attribute
    {
        return Attribute::get(fn(): string => str_replace('INV-', '', $this->number));
    }
}
