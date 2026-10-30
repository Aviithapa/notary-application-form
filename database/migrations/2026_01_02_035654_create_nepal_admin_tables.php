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
        // --------------------
        // Provinces
        // --------------------
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();

            $table->string('name_en');
            $table->string('name_np');

            $table->string('area_sq_km_en')->nullable();
            $table->string('area_sq_km_np')->nullable();

            $table->string('website')->nullable();
            $table->string('headquarter_en')->nullable();
            $table->string('headquarter_np')->nullable();

            $table->timestamps();
        });

        // --------------------
        // Districts
        // --------------------
        Schema::create('districts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('province_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name_en');
            $table->string('name_np');

            $table->string('area_sq_km_en')->nullable();
            $table->string('area_sq_km_np')->nullable();

            $table->string('website')->nullable();
            $table->string('headquarter_en')->nullable();
            $table->string('headquarter_np')->nullable();

            $table->timestamps();
        });

        // --------------------
        // Municipalities
        // --------------------
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('district_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('category_id');

            $table->string('name_en');
            $table->string('name_np');

            $table->string('area_sq_km_en')->nullable();
            $table->string('area_sq_km_np')->nullable();

            $table->string('website')->nullable();

            $table->timestamps();
        });

        // --------------------
        // Wards
        // --------------------
        Schema::create('wards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('municipality_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('ward_number');

            $table->timestamps();

            $table->unique(['municipality_id', 'ward_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards');
        Schema::dropIfExists('municipalities');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
    }
};
