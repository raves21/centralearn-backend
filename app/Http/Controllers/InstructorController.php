<?php

namespace App\Http\Controllers;

use App\Http\Resources\InstructorResource;
use App\Http\Services\InstructorService;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    protected $instructorService;

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
