<?php

namespace App\Http\Controllers;

use App\Http\Requests\LectureMaterial\Index;
use App\Http\Requests\LectureMaterial\Store;
use App\Http\Requests\LectureMaterial\StoreBulk;
use App\Http\Requests\LectureMaterial\Update;
use App\Http\Services\LectureMaterialService;
use Illuminate\Http\Request;

class LectureMaterialController extends Controller
{

    private $lectureMaterialService;

    public function __construct(LectureMaterialService $lectureMaterialService)
    {
        $this->lectureMaterialService = $lectureMaterialService;
    }

    public function index(Index $request)
    {
        return $this->lectureMaterialService->getAll(filters: $request->validated());
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->lectureMaterialService->create($request->validated());
    }

    public function storeBulk(StoreBulk $request)
    {
        return $this->lectureMaterialService->createBulk($request->validated());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update $request, string $id)
    {
        return $this->lectureMaterialService->updateById($id, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->lectureMaterialService->deleteById($id);
    }
}
