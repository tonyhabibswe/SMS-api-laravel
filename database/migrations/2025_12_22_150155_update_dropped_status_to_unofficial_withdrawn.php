<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('student_course_statuses')
            ->where('name', 'dropped')
            ->update([
                'name' => 'unofficial_withdrawn',
                'label' => 'Unofficial Withdrawn',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('student_course_statuses')
            ->where('name', 'unofficial_withdrawn')
            ->update([
                'name' => 'dropped',
                'label' => 'Dropped',
                'updated_at' => now(),
            ]);
    }
};
