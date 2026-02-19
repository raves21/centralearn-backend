<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssessmentMaterial\Index;
use App\Http\Requests\AssessmentMaterial\ProcessBulk;
use App\Http\Services\AssessmentMaterialService;

class AssessmentMaterialController extends Controller
{
    public function __construct(
        private AssessmentMaterialService $assessmentMaterialService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Index $request)
    {
        return $this->assessmentMaterialService->getAll($request->validated());
    }

    public function processBulk(ProcessBulk $request)
    {
        return $this->assessmentMaterialService->processBulk($request->validated());
    }
}
