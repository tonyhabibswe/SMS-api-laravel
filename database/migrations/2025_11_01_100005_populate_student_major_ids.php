<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if major_id column exists
        if (!Schema::hasColumn('students', 'major_id')) {
            Log::error('major_id column does not exist in students table');
            return;
        }

        // Check if majors table exists and has data
        if (!Schema::hasTable('majors')) {
            Log::error('majors table does not exist');
            return;
        }

        $majorCount = DB::table('majors')->count();
        if ($majorCount === 0) {
            Log::warning('majors table is empty, skipping student major_id population');
            return;
        }

        // Update students table to set major_id based on existing major string values
        try {
            $updatedCount = DB::table('students')
                ->whereNotNull('major')
                ->where('major', '!=', '')
                ->whereNull('major_id')
                ->update([
                    'major_id' => DB::raw('(
                        SELECT majors.id
                        FROM majors
                        WHERE majors.system_name = students.major
                        LIMIT 1
                    )')
                ]);

            Log::info("Updated {$updatedCount} students with major_id values");

            // Log any students that couldn't be matched
            $unmatchedStudents = DB::table('students')
                ->whereNull('major_id')
                ->whereNotNull('major')
                ->where('major', '!=', '')
                ->count();

            if ($unmatchedStudents > 0) {
                Log::warning("Found {$unmatchedStudents} students with majors that couldn't be matched to the majors table");

                // Show the unmatched majors for debugging
                $unmatchedMajors = DB::table('students')
                    ->select('major')
                    ->whereNull('major_id')
                    ->whereNotNull('major')
                    ->where('major', '!=', '')
                    ->distinct()
                    ->pluck('major')
                    ->toArray();

                Log::warning("Unmatched majors: " . implode(', ', $unmatchedMajors));
            }
        } catch (\Exception $e) {
            Log::error('Error updating student major_ids: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all major_id values to null
        DB::table('students')->update(['major_id' => null]);
    }
};
