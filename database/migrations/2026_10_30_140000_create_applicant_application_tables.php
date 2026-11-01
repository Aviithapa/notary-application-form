<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --------------------
        // Personal details (one row per applicant)
        // Photo and citizenship front/back are stored with the media library.
        // --------------------
        Schema::create('applicant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->string('name_np')->nullable();

            // Permanent address
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tole')->nullable();

            // Stored as Y-m-d strings in their own calendar
            $table->string('dob_ad', 10)->nullable();
            $table->string('dob_bs', 10)->nullable();

            $table->string('citizenship_number')->nullable();
            $table->foreignId('citizenship_issue_district_id')->nullable()->constrained('districts')->nullOnDelete();

            $table->string('occupation')->nullable();
            $table->string('gender')->nullable();
            $table->string('religion')->nullable();
            $table->string('mother_tongue')->nullable();
            $table->string('caste')->nullable();
            $table->text('huliya')->nullable();

            $table->string('mother_name')->nullable();
            $table->string('mother_name_np')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_name_np')->nullable();
            $table->string('grandfather_name')->nullable();
            $table->string('grandfather_name_np')->nullable();

            // Correspondence (पत्राचार गर्ने) or office address
            $table->foreignId('correspondence_district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('correspondence_municipality_id')->nullable()->constrained('municipalities')->nullOnDelete();
            $table->foreignId('correspondence_ward_id')->nullable()->constrained('wards')->nullOnDelete();
            $table->string('correspondence_tole')->nullable();
            $table->string('fax')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->timestamps();
        });

        // --------------------
        // Qualifications (many per applicant, documents via media library)
        // --------------------
        Schema::create('applicant_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('university_name')->nullable();
            $table->string('passed_year')->nullable();
            $table->string('level')->nullable();
            $table->string('division')->nullable();
            $table->string('percentage')->nullable();

            $table->timestamps();
        });

        // --------------------
        // Trainings (many per applicant, certificate via media library)
        // --------------------
        Schema::create('applicant_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('training_name')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('organization')->nullable();
            $table->string('issue_date_ad', 10)->nullable();
            $table->string('issue_date_bs', 10)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_trainings');
        Schema::dropIfExists('applicant_qualifications');
        Schema::dropIfExists('applicant_profiles');
    }
};
