<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChapterContent\ReorderBulk;
use App\Http\Requests\ChapterContent\Index;
use App\Http\Requests\ChapterContent\Store;
use App\Http\Requests\ChapterContent\Update;
use App\Http\Services\ChapterContentService;

class ChapterContentController extends Controller
{
    private $chapterContentService;

    public function __construct(ChapterContentService $chapterContentService)
    {
        $this->chapterContentService = $chapterContentService;
    }

    public function index(Index $request)
    {
        return $this->chapterContentService->getAll($request->validated());
    }

    public function store(Store $request)
    {
        return $this->chapterContentService->create($request->validated());
    }
    /**
     * Display the specified resource.
     */
    public function show(string $chapterContentId)
    {
        return $this->chapterContentService->findById($chapterContentId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update $request, string $chapterContentId)
    {
        return $this->chapterContentService->updateById($chapterContentId, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $chapterContentId)
    {
        return $this->chapterContentService->deleteById($chapterContentId);
    }

    public function reorderBulk(ReorderBulk $request)
    {
        return $this->chapterContentService->reorderBulk($request->validated());
    }

    public function updateAssessmentMaxAchievableScore(string $chapterContentId)
    {
        return $this->chapterContentService->updateAssessmentMaxAchievableScore($chapterContentId);
    }
}
