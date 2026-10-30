<?php

use App\Enums\RoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('roles')->where('name', RoleEnum::APPLICANT)->exists()) {
            DB::table('roles')->insert([
                'name' => RoleEnum::APPLICANT,
                'display_name' => 'Applicant',
                'description' => 'Self-registered applicant',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('name', RoleEnum::APPLICANT)->delete();
    }
};
