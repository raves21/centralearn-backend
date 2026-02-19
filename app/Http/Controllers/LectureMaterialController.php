<?php

namespace App\Http\Controllers;

use App\Http\Requests\LectureMaterial\Index;
use App\Http\Requests\LectureMaterial\ProcessBulk;
use App\Http\Services\LectureMaterialService;

class LectureMaterialController extends Controller
{

    public function __construct(
        private LectureMaterialService $lectureMaterialService
    ) {}

    public function index(Index $request)
    {
        return $this->lectureMaterialService->getAll(filters: $request->validated());
    }

    public function processBulk(ProcessBulk $request)
    {
        return $this->lectureMaterialService->processBulk($request->validated());
    }
}
