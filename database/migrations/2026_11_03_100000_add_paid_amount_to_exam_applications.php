<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Voucher, signature and thumbprints are stored with the media library on the application.
    public function up(): void
    {
        Schema::table('exam_applications', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->nullable()->after('is_double_fee');
        });
    }

    public function down(): void
    {
        Schema::table('exam_applications', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
