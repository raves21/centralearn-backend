<?php

namespace App\Http\Controllers;

use App\Http\Resources\SemesterResource;
use App\Http\Services\SemesterService;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    protected $semesterService;

    public function __construct(SemesterService $semesterService)
    {
        $this->semesterService = $semesterService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->semesterService->getAll();
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
    public function show(string $semesterId)
    {
        return $this->semesterService->findById($semesterId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Semester $semester)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Semester $semester)
    {
        //
    }
}
