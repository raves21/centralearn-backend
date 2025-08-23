<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionOption\Index;
use App\Http\Requests\QuestionOption\Store;
use App\Http\Requests\QuestionOption\Update;
use App\Http\Services\QuestionOptionService;

class QuestionOptionController extends Controller
{
    private $questionOptionService;

    public function __construct(QuestionOptionService $questionOptionService)
    {
        $this->questionOptionService = $questionOptionService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Index $request)
    {
        return $this->questionOptionService->getAll($request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->questionOptionService->create($request->validated());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update $request, string $id)
    {
        return $this->questionOptionService->updateById($id, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->questionOptionService->deleteById($id);
    }
}
