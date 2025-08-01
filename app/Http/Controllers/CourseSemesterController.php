<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseSemester\Index;
use App\Http\Requests\CourseSemester\Store;
use App\Http\Services\CourseSemesterService;
use Illuminate\Http\Request;

class CourseSemesterController extends Controller
{
    private $courseSemesterService;

    public function __construct(CourseSemesterService $courseSemesterService)
    {
        $this->courseSemesterService = $courseSemesterService;
    }

    public function index(Index $request)
    {
        return $this->courseSemesterService->getAll($request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->courseSemesterService->create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->courseSemesterService->findById($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->courseSemesterService->deleteById($id);
    }
}
