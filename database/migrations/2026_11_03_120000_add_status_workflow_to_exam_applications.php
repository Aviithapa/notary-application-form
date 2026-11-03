<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Application workflow: pending -> approved -> ready for admit card -> admit card generated
     * -> passed / failed, or rejected. Generating the admit card assigns a roll number per exam.
     */
    public function up(): void
    {
        Schema::table('exam_applications', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->unsignedInteger('roll_number')->nullable()->after('status');
            $table->timestamp('admit_card_generated_at')->nullable()->after('roll_number');
            // Latest note from the council, e.g. the reason for rejection
            $table->text('remarks')->nullable()->after('admit_card_generated_at');

            $table->unique(['exam_id', 'roll_number']);
            $table->index('status');
        });

        DB::table('exam_applications')->where('status', 'submitted')->update(['status' => 'pending']);

        Schema::create('exam_application_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('remarks')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_application_status_logs');

        DB::table('exam_applications')->where('status', 'pending')->update(['status' => 'submitted']);

        Schema::table('exam_applications', function (Blueprint $table) {
            $table->dropUnique(['exam_id', 'roll_number']);
            $table->dropIndex(['status']);
            $table->dropColumn(['roll_number', 'admit_card_generated_at', 'remarks']);
            $table->string('status')->default('submitted')->change();
        });
    }
};
