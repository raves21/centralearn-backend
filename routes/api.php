<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChapterContentController;
use App\Http\Controllers\CourseChapterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', fn() => response()->json(['message' => 'hello from /api']));

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::prefix('students')->group(function () {
        Route::get('me', [StudentController::class, 'currentUserStudentProfile']);
        Route::get('{student}/courses', [StudentController::class, 'getEnrolledCourses']);
    });

    Route::prefix('instructors')->group(function () {
        Route::get('me', [InstructorController::class, 'currentUserInstructorProfile']);
        Route::get('{instructor}/courses', [InstructorController::class, 'getAssignedCourses']);
    });

    Route::prefix('courses')->group(function () {
        Route::get('{course}/course-chapters', [CourseController::class, 'getChapters']);
    });

    Route::apiResources([
        'instructors' => InstructorController::class,
        'students' => StudentController::class,
        'departments' => DepartmentController::class,
        'programs' => ProgramController::class,
        'semesters' => SemesterController::class,
        'courses' => CourseController::class,
    ]);

    Route::apiResource('course-chapters', CourseChapterController::class)->except(['index']);
    Route::prefix('course-chapters')->group(function () {
        Route::get('{chapter}/chapter-contents', [CourseChapterController::class, 'getContents']);
    });

    Route::apiResource('chapter-contents', ChapterContentController::class)->except(['index']);
});
