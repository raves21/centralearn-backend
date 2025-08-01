<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\GetAssignedCourses;
use App\Http\Resources\InstructorResource;
use App\Http\Services\InstructorService;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        return $this->instructorService->getAll();
    }

    public function currentUserInstructorProfile()
    {
        return $this->instructorService->currentUserInstructorProfile();
    }

    public function getAssignedSemesters(string $instructorId)
    {
        return $this->instructorService->getAssignedSemesters($instructorId);
    }

    public function getAssignedCourses(string $instructorId, GetAssignedCourses $request)
    {
        $validated = $request->validated();
        return $this->instructorService->getAssignedCourses(
            instructorId: $instructorId,
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
    public function show(string $instructorId)
    {
        return $this->instructorService->findById($instructorId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instructor $instructor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instructor $instructor)
    {
        //
    }
}
