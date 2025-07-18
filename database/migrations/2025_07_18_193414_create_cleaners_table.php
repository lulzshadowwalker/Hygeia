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
        Schema::create('cleaners', function (Blueprint $table) {
            $table->id();
            $table->string('service_area');
            $table->json('available_days');
            $table->integer('max_hours_per_week')->nullable();
            $table->json('time_slots');
            $table->integer('years_of_experience');
            $table->boolean('has_cleaning_supplies');
            $table->boolean('comfortable_with_pets')->nullable();
            $table->json('previous_job_types');
            $table->integer('service_radius');
            $table->json('preferred_job_types');
            $table->boolean('agreed_to_terms');
            $table->foreignId('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaners');
    }
};
