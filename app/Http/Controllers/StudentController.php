<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\GetCoursesFilters;
use App\Http\Requests\Student\GetEnrolledCourses;
use App\Http\Resources\StudentResource;
use App\Http\Resources\UserResource;
use App\Models\Student;
use App\Http\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{

    private $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->studentService->getAll();
    }

    public function currentUserStudentProfile()
    {
        return $this->studentService->currentUserStudentProfile();
    }

    public function getEnrolledSemesters(string $studentId)
    {
        return $this->studentService->getEnrolledSemesters($studentId);
    }

    public function getEnrolledCourses(string $studentId, GetEnrolledCourses $request)
    {
        $validated = $request->validated();
        return $this->studentService->getEnrolledCourses(
            studentId: $studentId,
            filters: $validated
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $studentId)
    {
        return $this->studentService->findById($studentId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }
}
