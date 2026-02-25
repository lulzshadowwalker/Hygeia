<?php

use App\Enums\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_method', PaymentMethod::values())
                ->default(PaymentMethod::Cod->value)
                ->after('status');
            $table->timestamp('cash_received_at')->nullable()->after('payment_method');
            $table->decimal('cash_received_amount', 10, 2)->nullable()->after('cash_received_at');
            $table->char('cash_received_currency', 3)->nullable()->after('cash_received_amount');
            $table->unsignedBigInteger('cash_received_wallet_transaction_id')->nullable()->after('cash_received_currency');

            $table->index('cash_received_at');
            $table->index('cash_received_wallet_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['cash_received_at']);
            $table->dropIndex(['cash_received_wallet_transaction_id']);

            $table->dropColumn([
                'payment_method',
                'cash_received_at',
                'cash_received_amount',
                'cash_received_currency',
                'cash_received_wallet_transaction_id',
            ]);
        });
    }
};
