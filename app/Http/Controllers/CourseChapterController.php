<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseChapter\Store;
use App\Http\Requests\CourseChapter\Update;
use App\Http\Services\CourseChapterService;

class CourseChapterController extends Controller
{
    protected $courseChapterService;

    public function __construct(CourseChapterService $courseChapterService)
    {
        $this->courseChapterService = $courseChapterService;
    }

    public function getContents(string $courseChapterId)
    {
        return $this->courseChapterService->getContents($courseChapterId);
    }

    public function store(Store $request)
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
