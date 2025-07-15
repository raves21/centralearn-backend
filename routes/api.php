<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
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
    });

    Route::prefix('instructors')->group(function () {
        Route::get('me', [InstructorController::class, 'currentUserInstructorProfile']);
    });

    Route::apiResources([
        'instructors' => InstructorController::class,
        'students' => StudentController::class,
        'departments' => DepartmentController::class,
        'programs' => ProgramController::class,
        'semesters' => SemesterController::class
    ]);
});
