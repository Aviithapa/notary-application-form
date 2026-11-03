<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An exam is opened as an application call: applications are accepted from the start
        // date to the end date at the normal fee, then until the double-fee date at twice the fee.
        Schema::table('exams', function (Blueprint $table) {
            $table->string('application_start_ad', 10)->nullable()->after('title_np');
            $table->string('application_start_bs', 10)->nullable()->after('application_start_ad');
            $table->string('application_end_ad', 10)->nullable()->after('application_start_bs');
            $table->string('application_end_bs', 10)->nullable()->after('application_end_ad');
            $table->string('double_fee_end_ad', 10)->nullable()->after('application_end_bs');
            $table->string('double_fee_end_bs', 10)->nullable()->after('double_fee_end_ad');
        });

        Schema::create('exam_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('application_number')->nullable()->unique();
            // Fee charged at the time of applying (null for free services)
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->boolean('is_double_fee')->default(false);
            $table->string('status')->default('submitted');

            $table->timestamps();

            $table->unique(['exam_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_applications');

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'application_start_ad', 'application_start_bs',
                'application_end_ad', 'application_end_bs',
                'double_fee_end_ad', 'double_fee_end_bs',
            ]);
        });
    }
};
