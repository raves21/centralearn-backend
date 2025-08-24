<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseClass\Index;
use App\Http\Requests\CourseClass\Store;
use App\Http\Requests\CourseClass\Update;
use App\Http\Services\CourseClassService;

class CourseClassController extends Controller
{
    private $courseClassService;

    public function __construct(CourseClassService $courseClassService)
    {
        $this->courseClassService = $courseClassService;
    }

    public function index(Index $request)
    {
        return $this->courseClassService->getAll($request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->courseClassService->create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->courseClassService->findById($id);
    }

    public function update(Update $request, string $id)
    {
        return $this->courseClassService->updateById($id, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->courseClassService->deleteById($id);
    }
}
