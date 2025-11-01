<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extract unique majors from students table and insert into majors table
        $uniqueMajors = DB::table('students')
            ->select('major')
            ->distinct()
            ->whereNotNull('major')
            ->where('major', '!=', '')
            ->get();

        foreach ($uniqueMajors as $majorRecord) {
            DB::table('majors')->insertOrIgnore([
                'system_name' => $majorRecord->major,
                'label' => null, // Will be populated manually later
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove majors that were created from student data
        $studentMajors = DB::table('students')
            ->select('major')
            ->distinct()
            ->whereNotNull('major')
            ->where('major', '!=', '')
            ->pluck('major');

        DB::table('majors')
            ->whereIn('system_name', $studentMajors)
            ->whereNull('label') // Only remove those without custom labels
            ->delete();
    }
};
