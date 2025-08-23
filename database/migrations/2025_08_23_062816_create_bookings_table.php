<?php

use App\Enums\BookingStatus;
use App\Enums\BookingUrgency;
use App\Models\Client;
use App\Models\Pricing;
use App\Models\Service;
use App\Models\User;
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Service::class)->constrained()->restrictOnDelete();

            $table->decimal('selected_amount', 10, 2)->nullable();
            $table->foreignIdFor(Pricing::class)->constrained()->restrictOnDelete();

            $table->enum('urgency', BookingUrgency::values());
            $table->dateTime('scheduled_at')->nullable();
            $table->boolean('has_cleaning_material');

            $table->decimal('amount', 10, 2);
            $table->enum('status', BookingStatus::values())->default(BookingStatus::Pending->value);

            $table->index(['client_id', 'scheduled_at']);
            $table->index(['service_id', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
