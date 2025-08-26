<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Additional PDF-specific styles */
        @media print {
            @page {
                margin: 30mm;
            }

            body {
                font-family: sans-serif;
                color: #333;
            }
        }
    </style>
</head>

<body class="bg-white text-gray-900">

    <!-- Invoice Header -->
    <header class="text-center border-b-2 border-gray-200 pb-4 mb-6">
        <h1 class="text-2xl font-bold">{{ config('app.name') }} Invoice</h1>
        <p class="text-sm text-gray-600">{{ $invoice->strippedNumber }}</p>
        {{-- <p class="text-sm text-gray-600">Date: {{ $invoice->payment->created_at->format('d M Y') }}</p> --}}
    </header>

    <!-- Payer Info -->
    {{-- <section class="mb-4">
        <h2 class="text-lg font-semibold border-b border-gray-200 pb-2 mb-2">Billed To</h2>
        <p>{{ $invoice->payment->payable->payer()->fullName }}</p>
        <p>{{ $invoice->payment->payable->payer()->phone }}</p>
        @if($invoice->payment->payable->payer()->email)
        <p>{{ $invoice->payment->payable->payer()->email }}</p>
        @endif
    </section> --}}

    <!-- Invoice Items -->
    {{-- <section class="mb-6">
        <h2 class="text-lg font-semibold border-b border-gray-200 pb-2 mb-2">Invoice Details</h2>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="py-2 font-medium text-sm">Description</th>
                    <th class="py-2 font-medium text-sm text-right">Quantity</th>
                    <th class="py-2 font-medium text-sm text-right">Price</th>
                    <th class="py-2 font-medium text-sm text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->payment->payable->items() as $item)
                <tr class="border-b border-gray-100">
                    <td class="py-2">{{ $item->name() }}</td>
                    <td class="py-2 text-right">{{ $item->quantity() }}</td>
                    <td class="py-2 text-right">{{ $item->price()->formatTo('en_US') }}</td>
                    <td class="py-2 text-right">{{ $item->price()->multipliedBy($item->quantity())->formatTo('en_US') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </section> --}}

    <!-- Total Amount -->
    {{-- <section class="text-right mb-6">
        <p class="text-lg font-bold">Total: {{ $invoice->payment->payable->price()->formatTo('en_US') }}</p>
    </section> --}}

    <!-- Footer -->
    <footer class="text-center text-xs text-gray-500 border-t border-gray-200 pt-4">
        <p>Thank you for your business!</p>
        <p>{{ config('app.name') }} | All rights reserved</p>
    </footer>

</body>

</html>
