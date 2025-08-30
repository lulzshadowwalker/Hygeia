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
            $table->boolean('accepts_urgent_offers')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaners', function (Blueprint $table) {
            $table->dropColumn('accepts_urgent_offers')->default(true);
        });
    }
};
