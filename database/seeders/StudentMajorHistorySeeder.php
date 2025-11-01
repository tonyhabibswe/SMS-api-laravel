<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Semester;

class StudentMajorHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the current or most recent semester
        $currentSemester = Semester::orderBy('start_date', 'desc')->first();

        if (!$currentSemester) {
            $this->command->info('No semesters found. Skipping major history seeding.');
            return;
        }

        // Get all students with major_id set
        $students = Student::whereNotNull('major_id')->get();

        $historyRecords = [];
        $batchSize = 1000;

        foreach ($students as $student) {
            $historyRecords[] = [
                'student_id' => $student->id,
                'major_id' => $student->major_id,
                'semester_id' => $currentSemester->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches for better performance
            if (count($historyRecords) >= $batchSize) {
                DB::table('student_major_history')->insertOrIgnore($historyRecords);
                $historyRecords = [];
            }
        }

        // Insert remaining records
        if (!empty($historyRecords)) {
            DB::table('student_major_history')->insertOrIgnore($historyRecords);
        }

        $this->command->info('Student major history seeded for ' . $students->count() . ' students in semester: ' . $currentSemester->name);
    }
}
