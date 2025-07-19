<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseChapterController\Create;
use App\Http\Requests\CourseChapterController\Update;
use App\Http\Services\CourseChapterService;
use App\Models\CourseChapter;
use Illuminate\Http\Request;

class CourseChapterController extends Controller
{
    protected $courseChapterService;

    public function __construct(CourseChapterService $courseChapterService)
    {
        $this->courseChapterService = $courseChapterService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Create $request)
    {
        return $this->courseChapterService->create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $courseChapterId)
    {
        return $this->courseChapterService->findById($courseChapterId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update $request, string $courseChapterId)
    {
        return $this->courseChapterService->updateById($courseChapterId, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $courseChapterId)
    {
        return $this->courseChapterService->deleteById($courseChapterId);
    }
}
