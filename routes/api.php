<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChapterContentController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSemesterController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LectureMaterialController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AssesementMaterialController;
use App\Http\Controllers\QuestionOptionController;
use Illuminate\Support\Facades\Route;


Route::get('/', fn() => response()->json(['message' => 'hello from /api']));

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::prefix('students')->group(function () {
        Route::get('me', [StudentController::class, 'currentUserStudentProfile']);
        Route::get('{student}/courses-enrolled', [StudentController::class, 'getEnrolledCourses']);
        Route::get('{student}/semesters-enrolled', [StudentController::class, 'getEnrolledSemesters']);
    });

    Route::prefix('instructors')->group(function () {
        Route::get('me', [InstructorController::class, 'currentUserInstructorProfile']);
        Route::get('{instructor}/courses-assigned', [InstructorController::class, 'getAssignedCourses']);
        Route::get('{instructor}/semesters-assigned', [InstructorController::class, 'getAssignedSemesters']);
    });

    Route::apiResources([
        'instructors' => InstructorController::class,
        'students' => StudentController::class,
        'admins' => AdminController::class,
        'semesters' => SemesterController::class,
        'chapters' => ChapterController::class,
        'contents' => ChapterContentController::class,
    ]);

    Route::apiResource('departments', DepartmentController::class)->except('update');
    Route::post('departments/{department}', [DepartmentController::class, 'update']);

    Route::apiResource('programs', ProgramController::class)->except('update');
    Route::post('programs/{program}', [ProgramController::class, 'update']);

    Route::apiResource('courses', CourseController::class)->except('update');
    Route::post('courses/{course}', [CourseController::class, 'update']);

    Route::apiResource('course-semesters', CourseSemesterController::class)->except('update');
    Route::post('course-semesters/{course_semester}', [CourseSemesterController::class, 'update']);

    Route::apiResource('lecture-materials', LectureMaterialController::class)->except(['update', 'show']);
    Route::post('lecture-materials/{lecture_material}', [LectureMaterialController::class, 'update']);

    Route::apiResource('assessment-materials', AssesementMaterialController::class)->except(['update', 'show']);
    Route::post('assessment-materials/{assessment_material}', [AssesementMaterialController::class, 'update']);

    Route::apiResource('question-options', QuestionOptionController::class)->except(['update', 'show']);
    Route::post('question-options/{question_option}', [QuestionOptionController::class, 'update']);
});
