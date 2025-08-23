<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssessmentMaterial\Index;
use App\Http\Requests\AssessmentMaterial\Store;
use App\Http\Requests\AssessmentMaterial\Update;
use App\Http\Services\AssessmentMaterialService;

class AssesementMaterialController extends Controller
{
    private $assessmentMaterialService;

    public function __construct(AssessmentMaterialService $assessmentMaterialService)
    {
        $this->assessmentMaterialService = $assessmentMaterialService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Index $request)
    {
        return $this->assessmentMaterialService->getAll($request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->assessmentMaterialService->create($request->validated());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update $request, string $id)
    {
        return $this->assessmentMaterialService->updateById($id, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->assessmentMaterialService->deleteById($id);
    }
}
