<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chapter\Index;
use App\Http\Requests\Chapter\Store;
use App\Http\Requests\Chapter\Update;
use App\Http\Services\ChapterService;

class ChapterController extends Controller
{
    private $chapterService;

    public function __construct(ChapterService $chapterService)
    {
        $this->chapterService = $chapterService;
    }

    public function index(Index $request)
    {
        return $this->chapterService->getAll($request->validated());
    }

    public function store(Store $request)
    {
        return $this->chapterService->create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $chapterId)
    {
        return $this->chapterService->findById($chapterId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update $request, string $chapterId)
    {
        return $this->chapterService->updateById($chapterId, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $chapterId)
    {
        return $this->chapterService->deleteById($chapterId);
    }
}
