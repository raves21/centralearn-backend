<?php

namespace App\Http\Controllers;

use App\Http\Requests\Semester\Index;
use App\Http\Requests\Semester\Store;
use App\Http\Requests\Semester\Update;
use App\Http\Requests\Semester\UpdateSemesterGetMinMaxTimestamps;
use App\Http\Services\SemesterService;

class SemesterController extends Controller
{
    private $semesterService;

    public function __construct(SemesterService $semesterService)
    {
        $this->semesterService = $semesterService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Index $request)
    {
        return $this->semesterService->getAll($request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->semesterService->create($request->validated());
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
    public function update(Update $request, string $semesterId)
    {
        return $this->semesterService->updateById($semesterId, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $semesterId)
    {
        return $this->semesterService->deleteById($semesterId);
    }

    public function updateSemesterGetMinMaxTimestamps(UpdateSemesterGetMinMaxTimestamps $request)
    {
        return $this->semesterService->updateSemesterGetMinMaxTimestamps(id: $request->validated()['semester_id']);
    }

    public function createSemesterGetMinMaxTimestamps()
    {
        return $this->semesterService->createSemesterGetMinMaxTimestamps();
    }
}
