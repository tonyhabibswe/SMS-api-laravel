<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSectionController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\SemesterController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


//Auth routes
Route::post('login', [AuthController::class, 'login']);

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('test', [AuthController::class, 'test'])->middleware('auth:api');

//Semester routes
Route::get('semesters', [SemesterController::class, 'list'])->middleware('auth:api');
Route::post('semesters', [SemesterController::class, 'create'])->middleware('auth:api');
Route::put('semester/{id}', [SemesterController::class, 'edit'])->where('id', '[0-9]+')->middleware('auth:api');
Route::delete('semester/{id}', [SemesterController::class, 'delete'])->where('id', '[0-9]+')->middleware('auth:api');
Route::get('semester/{id}/courses', [SemesterController::class, 'listCoursesBySemesterId'])->where('id', '[0-9]+')->middleware('auth:api');

//Holiday routes
Route::get('semester/{id}/holidays', [HolidayController::class, 'listHolidays'])->where('id', '[0-9]+')->middleware('auth:api');
Route::post('semester/{id}/holidays', [HolidayController::class, 'createHoliday'])->where('id', '[0-9]+')->middleware('auth:api');
Route::put('holiday/{id}', [HolidayController::class, 'updateHoliday'])->where('id', '[0-9]+')->middleware('auth:api');



//Course routes
Route::get('courses', [CourseController::class, 'list'])->middleware('auth:api');
Route::post('courses', [CourseController::class, 'create'])->middleware('auth:api');
Route::put('course/{id}', [CourseController::class, 'edit'])->where('id', '[0-9]+')->middleware('auth:api');
Route::delete('course/{id}', [CourseController::class, 'delete'])->where('id', '[0-9]+')->middleware('auth:api');

//Course Section routes
Route::post('course-sections', [CourseSectionController::class, 'create'])->middleware('auth:api');
Route::put('course-section/{id}', [CourseSectionController::class, 'edit'])->where('id', '[0-9]+')->middleware('auth:api');
Route::delete('course-section/{id}', [CourseSectionController::class, 'delete'])->where('id', '[0-9]+')->middleware('auth:api');
Route::post('course-section/{id}/import-students', [CourseSectionController::class, 'importStudents']) ->where('id', '[0-9]+')->middleware('auth:api');
Route::post('course-section/{id}/session', [CourseSectionController::class, 'createSession'])->where('id', '[0-9]+')->middleware('auth:api');
Route::get('course-section/{id}/sessions', [CourseSectionController::class, 'getSessions'])->where('id', '[0-9]+')->middleware('auth:api');
Route::get('course-section/{id}/students', [CourseSectionController::class, 'getStudents'])->where('id', '[0-9]+')->middleware('auth:api');

//Attendance routes
Route::get('course-session/{id}/attendances/list-students', [AttendanceController::class, 'listStudentsAttendance'])->where('id', '[0-9]+')->middleware('auth:api');
Route::put('attendance/{id}', [AttendanceController::class, 'update'])->where('id', '[0-9]+')->middleware('auth:api');
Route::put('course-session/{id}/attendances/bulk', [AttendanceController::class, 'updateBulkAttendanceValues'])->where('id', '[0-9]+')->middleware('auth:api');
Route::put('course-session/{id}/attendances/all', [AttendanceController::class, 'updateAllAttendanceValues'])->where('id', '[0-9]+')->middleware('auth:api');
Route::get('course-section/{id}/export-attendance', [AttendanceController::class, 'exportAttendance'])->where('id', '[0-9]+')->middleware('auth:api');
