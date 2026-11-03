<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --------------------
        // Services offered by the council (e.g. new notary licence, renewal)
        // --------------------
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('name_np');
            $table->text('description')->nullable();
            $table->text('description_np')->nullable();

            $table->string('purpose');
            $table->string('icon')->default('bi-briefcase');

            $table->boolean('is_fee_based')->default(false);
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->boolean('requires_exam')->default(false);
            $table->boolean('requires_application_form')->default(true);

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });

        // --------------------
        // Exams held for a service that requires one
        // --------------------
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();

            $table->string('title');
            $table->string('title_np')->nullable();

            // Stored as Y-m-d strings in their own calendar
            $table->string('exam_date_ad', 10)->nullable();
            $table->string('exam_date_bs', 10)->nullable();
            $table->time('start_time')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();

            $table->string('venue')->nullable();
            $table->string('venue_np')->nullable();

            $table->unsignedSmallInteger('full_marks')->nullable();
            $table->unsignedSmallInteger('pass_marks')->nullable();

            $table->string('status')->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
        Schema::dropIfExists('services');
    }
};
