<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\EnrollToClass;
use App\Http\Requests\Student\GetEnrollableClasses;
use App\Http\Requests\Student\GetEnrolledClasses;
use App\Http\Requests\Student\Store;
use App\Http\Requests\Student\UnenrollToClass;
use App\Http\Requests\Student\Update;
use App\Http\Services\StudentService;

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

    public function getEnrolledClasses(string $studentId, GetEnrolledClasses $request)
    {
        return $this->studentService->getEnrolledClasses(
            studentId: $studentId,
            filters: $request->validated()
        );
    }

    public function getEnrollableClasses(GetEnrollableClasses $request, string $studentId)
    {
        $validated = $request->validated();
        return $this->studentService->getEnrollableClasses(
            studentId: $studentId,
            semesterId: $validated['semester_id']
        );
    }

    public function enrollToClass(string $studentId, EnrollToClass $request)
    {
        return $this->studentService->enrollToClass(
            studentId: $studentId,
            classId: $request->validated()['course_class_id']
        );
    }

    public function unenrollToClass(string $studentId, UnenrollToClass $request)
    {
        return $this->studentService->unenrollToClass(
            studentId: $studentId,
            classId: $request->validated()['course_class_id']
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->studentService->create($request->validated());
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
    public function update(Update $request, string $studentId)
    {
        return $this->studentService->updateById($studentId, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $studentId)
    {
        return $this->studentService->deleteById($studentId);
    }
}
