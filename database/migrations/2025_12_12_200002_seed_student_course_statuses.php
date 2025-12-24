<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('student_course_statuses')->insert([
            ['id' => 1, 'name' => 'enrolled', 'label' => 'Enrolled', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'dropped', 'label' => 'Dropped', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'auditor', 'label' => 'Auditor', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'withdrawn', 'label' => 'W', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'incomplete', 'label' => 'I', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('student_course_statuses')->whereIn('id', [1, 2, 3, 4, 5])->delete();
    }
};
