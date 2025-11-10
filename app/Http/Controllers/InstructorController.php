<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\AssignToClass;
use App\Http\Requests\Instructor\GetAssignableClasses;
use App\Http\Requests\Instructor\GetAssignedCourses;
use App\Http\Requests\Instructor\Index;
use App\Http\Requests\Instructor\Store;
use App\Http\Requests\Instructor\UnassignToClass;
use App\Http\Requests\Instructor\Update;
use App\Http\Services\InstructorService;

class InstructorController extends Controller
{
    private $instructorService;

    public function __construct(InstructorService $instructorService)
    {
        $this->instructorService = $instructorService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Index $request)
    {
        return $this->instructorService->getAll($request->validated());
    }

    public function currentUserInstructorProfile()
    {
        return $this->instructorService->currentUserInstructorProfile();
    }

    public function getAssignedSemesters(string $instructorId)
    {
        return $this->instructorService->getAssignedSemesters($instructorId);
    }

    public function getAssignedClasses(string $instructorId, GetAssignedCourses $request)
    {
        return $this->instructorService->getAssignedClasses(
            instructorId: $instructorId,
            filters: $request->validated()
        );
    }

    public function getAssignableClasses(GetAssignableClasses $request, string $instructorId)
    {
        return $this->instructorService->getAssignableClasses(
            instructorId: $instructorId,
            filters: $request->validated()
        );
    }

    public function assignToClass(string $instructorId, AssignToClass $request)
    {
        return $this->instructorService->assignToClass(
            instructorId: $instructorId,
            classId: $request->validated()['course_class_id']
        );
    }

    public function unassignToClass(string $instructorId, UnassignToClass $request)
    {
        return $this->instructorService->unassignToClass(
            instructorId: $instructorId,
            classId: $request->validated()['course_class_id']
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->instructorService->create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $instructorId)
    {
        return $this->instructorService->findById($instructorId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update $request, string $instructorId)
    {
        return $this->instructorService->updateById($instructorId, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $instructorId)
    {
        return $this->instructorService->deleteById($instructorId);
    }
}
