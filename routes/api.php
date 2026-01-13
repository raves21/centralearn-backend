<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChapterContentController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseClassController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LectureMaterialController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AssesementMaterialController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\QuestionOptionController;
use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;


Route::get('/', fn() => response()->json(['message' => 'hello from /api']));

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::prefix('students')->group(function () {
        Route::get('me', [StudentController::class, 'currentUserStudentProfile']);
        Route::get('{student}/classes-enrolled', [StudentController::class, 'getEnrolledClasses']);
        Route::get('{student}/semesters-enrolled', [StudentController::class, 'getEnrolledSemesters']);
        Route::get('{student}/enrollable-classes', [StudentController::class, 'getEnrollableClasses']);
        Route::post('{student}/enroll-to-class', [StudentController::class, 'enrollToClass']);
        Route::post('{student}/unenroll-to-class', [StudentController::class, 'unenrollToClass']);
    });

    Route::prefix('instructors')->group(function () {
        Route::get('me', [InstructorController::class, 'currentUserInstructorProfile']);
        Route::get('{instructor}/classes-assigned', [InstructorController::class, 'getAssignedClasses']);
        Route::get('{instructor}/semesters-assigned', [InstructorController::class, 'getAssignedSemesters']);
        Route::get('{instructor}/assignable-classes', [InstructorController::class, 'getAssignableClasses']);
        Route::post('{instructor}/assign-to-class', [InstructorController::class, 'assignToClass']);
        Route::post('{instructor}/unassign-to-class', [InstructorController::class, 'unassignToClass']);
    });

    Route::prefix('semesters')->group(function () {
        Route::get('update-semester-get-minmax-timestamps', [SemesterController::class, 'updateSemesterGetMinMaxTimestamps']);
        Route::get('create-semester-get-minmax-timestamps', [SemesterController::class, 'createSemesterGetMinMaxTimestamps']);
    });

    Route::apiResources([
        'semesters' => SemesterController::class
    ]);

    Route::apiResource('admins', AdminController::class)->except('update');
    Route::post('admins/{admin}', [AdminController::class, 'update']);

    Route::apiResource('instructors', InstructorController::class)->except('update');
    Route::post('instructors/{instructor}', [InstructorController::class, 'update']);

    Route::apiResource('students', StudentController::class)->except('update');
    Route::post('students/{student}', [StudentController::class, 'update']);

    Route::apiResource('departments', DepartmentController::class)->except('update');
    Route::post('departments/{department}', [DepartmentController::class, 'update']);

    Route::apiResource('programs', ProgramController::class)->except('update');
    Route::post('programs/{program}', [ProgramController::class, 'update']);

    Route::apiResource('courses', CourseController::class)->except('update');
    Route::post('courses/{course}', [CourseController::class, 'update']);

    Route::apiResource('sections', SectionController::class)->except(['update']);
    Route::post('sections/{section}', [SectionController::class, 'update']);

    Route::apiResource('course-classes', CourseClassController::class)->except('update');
    Route::get('course-classes/{course_class}/chapter-count', [CourseClassController::class, 'getChapterCount']);
    Route::post('course-classes/{course_class}', [CourseClassController::class, 'update']);

    Route::apiResource('chapters', ChapterController::class)->except('update');
    Route::get('chapters/{chapter}/content-count', [ChapterController::class, 'getContentCount']);
    Route::post('chapters/reorder-bulk', [ChapterController::class, 'reorderBulk']);
    Route::post('chapters/{chapter}', [ChapterController::class, 'update']);

    Route::apiResource('contents', ChapterContentController::class)->except('update');
    Route::post('contents/reorder-bulk', [ChapterContentController::class, 'reorderBulk']);
    Route::post('contents/{content}', [ChapterContentController::class, 'update']);

    Route::apiResource('assessments', AssessmentController::class)->only('update');

    Route::apiResource('lecture-materials', LectureMaterialController::class)->except(['update', 'show']);
    Route::post('lecture-materials/process-bulk', [LectureMaterialController::class, 'processBulk']);
    Route::post('lecture-materials/{lecture_material}', [LectureMaterialController::class, 'update']);

    Route::apiResource('assessment-materials', AssesementMaterialController::class)->except(['update', 'show']);
    Route::post('assessment-materials/{assessment_material}', [AssesementMaterialController::class, 'update']);

    Route::apiResource('question-options', QuestionOptionController::class)->except(['update', 'show']);
    Route::post('question-options/{question_option}', [QuestionOptionController::class, 'update']);
});
