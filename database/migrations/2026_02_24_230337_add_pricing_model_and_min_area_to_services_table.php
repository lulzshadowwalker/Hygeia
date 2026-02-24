<?php

use App\Enums\ServicePricingModel;
use App\Enums\ServiceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->enum('pricing_model', ServicePricingModel::values())
                ->nullable()
                ->after('type');
            $table->integer('min_area')
                ->nullable()
                ->after('price_per_meter');
        });

        DB::table('services')
            ->where('type', ServiceType::Residential->value)
            ->update([
                'pricing_model' => ServicePricingModel::AreaRange->value,
                'price_per_meter' => null,
            ]);

        DB::table('services')
            ->where('type', ServiceType::Commercial->value)
            ->update([
                'pricing_model' => ServicePricingModel::AreaRange->value,
            ]);

        Schema::table('services', function (Blueprint $table) {
            $table->enum('pricing_model', ServicePricingModel::values())
                ->default(ServicePricingModel::AreaRange->value)
                ->nullable(false)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['pricing_model', 'min_area']);
        });
    }
};
