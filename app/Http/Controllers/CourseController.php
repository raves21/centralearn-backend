<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\Index;
use App\Http\Requests\Course\Store;
use App\Http\Requests\Course\Update;
use App\Http\Services\CourseService;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        private CourseService $courseService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Index $request)
    {
        return $this->courseService->getAll(filters: $request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->courseService->create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $courseId)
    {
        return $this->courseService->findById($courseId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update $request, string $courseId)
    {
        return $this->courseService->updateById($courseId, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $courseId)
    {
        return $this->courseService->deleteById($courseId);
    }
}
