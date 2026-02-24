<?php

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
        Schema::table('extras', function (Blueprint $table) {
            $table->char('currency', 3)->nullable()->default('HUF')->after('amount');
        });

        Schema::table('pricings', function (Blueprint $table) {
            $table->char('currency', 3)->nullable()->default('HUF')->after('amount');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->char('currency', 3)->nullable()->default('HUF')->after('price_per_meter');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->char('currency', 3)->nullable()->default('HUF')->after('amount');
        });

        Schema::table('booking_extra', function (Blueprint $table) {
            $table->char('currency', 3)->nullable()->default('HUF')->after('amount');
        });

        DB::table('extras')->update(['currency' => 'HUF']);
        DB::table('pricings')->update(['currency' => 'HUF']);
        DB::table('services')->update(['currency' => 'HUF']);
        DB::table('bookings')->update(['currency' => 'HUF']);
        DB::table('booking_extra')->update(['currency' => 'HUF']);

        Schema::table('extras', function (Blueprint $table) {
            $table->char('currency', 3)->default('HUF')->nullable(false)->change();
        });

        Schema::table('pricings', function (Blueprint $table) {
            $table->char('currency', 3)->default('HUF')->nullable(false)->change();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->char('currency', 3)->default('HUF')->nullable(false)->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->char('currency', 3)->default('HUF')->nullable(false)->change();
        });

        Schema::table('booking_extra', function (Blueprint $table) {
            $table->char('currency', 3)->default('HUF')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_extra', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('pricings', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('extras', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
