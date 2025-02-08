<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseSectionCreateRequest;
use App\Http\Requests\CourseSectionEditRequest;
use App\Http\Requests\ImportStudentsRequest;
use App\Http\Requests\SessionCreateRequest;
use App\Http\Requests\StudentCreateRequest;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseSession;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseSectionController extends Controller
{
    public function create(CourseSectionCreateRequest $request): JsonResponse
    {
        // Find the semester
        $semester = Semester::with('holidays')->find($request->semester_id);
        if (!$semester) {
            return response()->json(['message' => 'Semester not found'], 404);
        }
        $course = Course::find($request->course_id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Get holiday dates as an array of date strings for quick comparison
        $holidayDates = $semester->holidays->pluck('date')->toArray();

        // Create the course with formatted time string
        $courseSectionTime = implode('', array_map('getDayAbbreviation', $request->course_days))
        . " {$request->start_session_time}-{$request->end_session_time}";

        $courseSection = CourseSection::create([
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'section_code' => $request->section_code,
            'time' => $courseSectionTime,
        ]);

        // Generate sessions for specified days between semester start and end dates
        $sessions = [];
        foreach (range(0, $semester->end_date->diffInDays($semester->start_date)) as $dayOffset) {
            $date = $semester->start_date->copy()->addDays($dayOffset);

             // Skip this date if it’s a holiday
            if (in_array($date->toDateString(), $holidayDates)) {
                continue;
            }

            if (in_array($date->format('l'), $request->course_days)) {
                $startDateTime = $date->copy()->setTimeFromTimeString($request->start_session_time);
                $endDateTime = $date->copy()->setTimeFromTimeString($request->end_session_time);

                $sessions[] = new CourseSession([
                    'session_start' => $startDateTime,
                    'session_end' => $endDateTime,
                    'room' => $request->room,
                ]);
            }
        }

        // Save course and attach sessions
        $courseSection->sessions()->saveMany($sessions);

        return response()->json($course->load('sessions'), 201);
    }


    public function edit(int $id, CourseSectionEditRequest $request): JsonResponse
    {
        // Find the course section by ID
        $courseSection = CourseSection::find($id);
        if (!$courseSection) {
            return response()->json(['message' => 'Course section not found'], 404);
        }

        // Update required fields
        $courseSection->section_code = $request->section_code;

        // Save the updated course
        $courseSection->save();

        return response()->json($courseSection, 200);
    }

    public function delete(int $id): JsonResponse
    {
        // Find the course section by its ID
        $courseSection = CourseSection::find($id);
        
        // If no course is found, return a 404 error response
        if (!$courseSection) {
            return response()->json(['message' => 'Course section not found'], 404);
        }

        // Delete the course section
        $courseSection->delete();

        // Return a success message
        return response()->json(['message' => 'Course section deleted successfully'], 200);
    }


    /**
     * Create a student and add attendances for all sessions of the course.
     *
     * Route: POST /course-section/{id}/student
     *
     * @param  StudentCreateRequest  $request
     * @param  int  $id  The course_section id.
     * @return JsonResponse
     */
    public function createStudent(StudentCreateRequest $request, int $id): JsonResponse
    {
        // Retrieve the validated student data
        $data = $request->validated();

        // Check if the student already exists by student_id.
        $student = Student::where('student_id', $data['student_id'])->first();
        if (!$student) {
            $student = Student::create([
                'student_id'  => $data['student_id'],
                'first_name'  => $data['first_name'],
                'father_name' => $data['father_name'],
                'last_name'   => $data['last_name'],
                'major'       => $data['major'],
                'email'       => $data['email'],
                'campus'      => $data['campus'],
            ]);
        }

        // Retrieve sessions where the related course has the provided id.
        $sessions = CourseSession::with('courseSection')
            ->whereHas('courseSection', function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->get();

        if ($sessions->isEmpty()) {
            return response()->json(['message' => "This course section doesn't have any session created"], 400);
        }

        // Check if an attendance already exists for the first session for this student.
        $firstSession = $sessions->first();
        $attendanceInDb = Attendance::where('course_session_id', $firstSession->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$attendanceInDb) {
            // Create an attendance record for each session.
            foreach ($sessions as $session) {
                Attendance::create([
                    'course_session_id' => $session->id,
                    'student_id'        => $student->id,
                    // Optionally set a default value for 'value', e.g. null
                    'value'             => null,
                ]);
            }
        }

        // Return the created or found student with a 201 status code.
        return response()->json($student, 201);
    }


    public function importStudents(ImportStudentsRequest $request, int $id)
    {
        // The file has been validated by ImportStudentsRequest
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return response()->json(['message' => 'Unable to open the file.'], 500);
        }
        
        // Skip the first 4 lines
        for ($i = 0; $i < 4; $i++) {
            fgetcsv($handle);
        }
        
        $csvStudents = [];
        
        // Read the remaining CSV rows
        while (($row = fgetcsv($handle)) !== false) {
            // Ensure that we have enough columns (expecting 7 columns)
            if (count($row) < 7) {
                continue;
            }
            $csvStudents[] = [
                'student_id' => $row[0],
                'first_name' => $row[1],
                'father_name' => $row[2],
                'last_name' => $row[3],
                'major' => $row[4],
                'email' => $row[5],
                'campus' => $row[6],
            ];
        }
        fclose($handle);
        
        if (empty($csvStudents)) {
            return response()->json(['message' => 'No student records found in CSV.'], 400);
        }
        
        try {
            DB::transaction(function () use ($csvStudents, $id) {
                // Extract unique student_ids from CSV records
                $recordIds = collect($csvStudents)->pluck('student_id')->unique()->toArray();
                
                // Retrieve students already in the database with these student_ids
                $existingStudents = Student::whereIn('student_id', $recordIds)->get();
                $existingStudentIds = $existingStudents->pluck('student_id')->toArray();
                
                // Filter out records for new students (not already in the database)
                $newStudentsData = collect($csvStudents)
                    ->filter(function ($student) use ($existingStudentIds) {
                        return !in_array($student['student_id'], $existingStudentIds);
                    })
                    ->values()
                    ->all();
                
                // Insert new students and collect the resulting models
                $newStudents = [];
                foreach ($newStudentsData as $data) {
                    $newStudents[] = Student::create($data);
                }
                
                // Combine new students with those already existing
                $allStudents = $existingStudents->merge($newStudents);
                
                // Retrieve sessions for the given course section id
                $sessions = CourseSession::where('course_section_id', $id)->get();
                if ($sessions->isEmpty()) {
                    throw new \Exception("This course section doesn't have any session created");
                }
                
                // For each session, create attendance records for each student
                foreach ($sessions as $session) {
                    foreach ($allStudents as $student) {
                        Attendance::create([
                            'course_session_id' => $session->id,
                            'student_id' => $student->id,
                            'value' => null, // Set default attendance value if necessary
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        
        return response()->json(['message' => 'Students imported and attendance records created successfully.'], 200);
    }


    public function createSession(SessionCreateRequest $request, int $id)
    {
        // The request has already been validated by SessionCreateRequest
        $data = $request->validated();
        
        // Find the course by its id
        $courseSection = CourseSection::find($id);
        if (!$courseSection) {
            return response()->json(['message' => 'Course section not found!'], 404);
        }
        
        try {
            DB::transaction(function () use ($data, $courseSection, &$session) {
                // Create the new session associated with the course section
                $session = CourseSession::create([
                    'course_section_id'     => $courseSection->id,
                    'room'          => $data['room'],
                    'session_start' => $data['session_start'],
                    'session_end'   => $data['session_end'],
                ]);

                // Retrieve distinct student IDs already enrolled in this course section
                // via previous attendance records
                $studentIds = Attendance::whereHas('session', function ($query) use ($courseSection) {
                    $query->where('course_section_id', $courseSection->id);
                })->distinct()->pluck('student_id');

                // For each student, create a new attendance record for the new session
                foreach ($studentIds as $studentId) {
                    Attendance::create([
                        'course_session_id' => $session->id,
                        'student_id'        => $studentId,
                        'value'             => null, // or a default value if needed
                    ]);
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        
        // Return the new session with a 201 (Created) status code
        return response()->json($session, 201);
    }

    public function getSessions(int $id)
    {
        // Retrieve sessions where the session start date is <= today's date and matches the given course id
        $sessions = CourseSession::whereDate('session_start', '<=', now()->toDateString())
            ->where('course_section_id', $id)
            ->orderByDesc('session_start')
            ->get();

        return response()->json($sessions);
    }

    public function getStudents(int $id)
    {
        // Join attendances with course_sessions, courses, and students to filter by course id
        $results = DB::table('attendances')
            ->join('course_sessions', 'attendances.course_session_id', '=', 'course_sessions.id')
            ->join('course_sections', 'course_sessions.course_section_id', '=', 'course_sections.id')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->where('course_sections.id', '=', $id)
            ->select(
                'students.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                'students.major',
                'students.email',
                'students.campus',
                DB::raw('SUM(CASE WHEN attendances.value = "abscent" THEN 1 ELSE 0 END) as abscences'),
                DB::raw('SUM(CASE WHEN attendances.value IS NOT NULL THEN 1 ELSE 0 END) as sessions'),
                DB::raw('COUNT(attendances.id) as total_sessions')
            )
            ->groupBy(
                'students.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                'students.major',
                'students.email',
                'students.campus'
            )
            ->get();

        return response()->json($results);
    }

}
