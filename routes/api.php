<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSectionController;
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
Route::put('semesters', [SemesterController::class, 'edit'])->middleware('auth:api');
Route::delete('semester/{id}', [SemesterController::class, 'delete'])->where('id', '[0-9]+')->middleware('auth:api');
Route::get('semester/{id}/courses', [SemesterController::class, 'listCoursesBySemesterId'])->where('id', '[0-9]+')->middleware('auth:api');

//Course routes
Route::get('courses', [CourseController::class, 'list'])->middleware('auth:api');
Route::post('courses', [CourseController::class, 'create'])->middleware('auth:api');
Route::put('courses/{id}', [CourseController::class, 'edit'])->where('id', '[0-9]+')->middleware('auth:api');
Route::delete('courses/{id}', [CourseController::class, 'delete'])->where('id', '[0-9]+')->middleware('auth:api');

//Course Section routes
Route::post('course-section/{id}', [CourseSectionController::class, 'create'])->where('id', '[0-9]+')->middleware('auth:api');
Route::put('course-section/{id}', [CourseSectionController::class, 'edit'])->where('id', '[0-9]+')->middleware('auth:api');
Route::delete('course-section/{id}', [CourseSectionController::class, 'delete'])->where('id', '[0-9]+')->middleware('auth:api');
Route::post('course-section/{id}/import-students', [CourseSectionController::class, 'importStudents']) ->where('id', '[0-9]+')->middleware('auth:api');
Route::post('course-section/{id}/session', [CourseSectionController::class, 'createSession'])->where('id', '[0-9]+')->middleware('auth:api');
Route::get('course-section/{id}/sessions', [CourseSectionController::class, 'getSessions'])->where('id', '[0-9]+')->middleware('auth:api');
Route::get('course-section/{id}/students', [CourseSectionController::class, 'getStudents'])->where('id', '[0-9]+')->middleware('auth:api');

//Attendance routes
Route::put('attendances/{id}', [AttendanceController::class, 'update'])->where('id', '[0-9]+')->middleware('auth:api');
