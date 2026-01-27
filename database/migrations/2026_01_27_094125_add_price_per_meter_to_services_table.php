<?php

use App\Enums\ServiceType;
use App\Models\Pricing;
use App\Models\Service;
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
            $table->foreignIdFor(Pricing::class)->nullable()->nullOnDelete()->change();
            $table->decimal('price_per_meter', 8, 2)->nullable()->after('pricing_id');
        });

        $residentialServices = Service::where('type', ServiceType::Residential)->get();
        foreach ($residentialServices as $service) {
            $service->pricings()->delete();
        }

        Schema::table('services', function (Blueprint $table) {
            $table->decimal('price_per_meter', 8, 2)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('price_per_meter');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('price_per_meter');
        });
    }
};
