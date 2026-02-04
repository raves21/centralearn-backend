<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssessmentMaterial\Index;
use App\Http\Requests\AssessmentMaterial\ProcessBulk;
use App\Http\Services\AssessmentMaterialService;

class AssessmentMaterialController extends Controller
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

    public function processBulk(ProcessBulk $request)
    {
        return $this->assessmentMaterialService->processBulk($request->validated());
    }
}
