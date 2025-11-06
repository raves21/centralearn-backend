<?php

namespace App\Http\Controllers;

use App\Http\Requests\Section\Index;
use App\Http\Requests\Section\Store;
use App\Http\Requests\Section\Update;
use App\Http\Services\SectionService;

class SectionController extends Controller
{
    private $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Index $request)
    {
        return $this->sectionService->getAll($request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->sectionService->create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->sectionService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update $request, string $id)
    {
        return $this->sectionService->updateById($id, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->sectionService->deleteById($id);
    }
}
