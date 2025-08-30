<?php

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
        Schema::table('cleaners', function (Blueprint $table) {
            $table->dropColumn('service_area');
            $table->foreignId('service_area_id')->nullable()->constrained('districts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaners', function (Blueprint $table) {
            $table->string('service_area')->nullable();
            $table->dropForeign(['service_area_id']);
            $table->dropColumn('service_area_id');
        });
    }
};
