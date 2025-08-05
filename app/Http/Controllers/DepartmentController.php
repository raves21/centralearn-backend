<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\Index;
use App\Http\Requests\Department\Store;
use App\Http\Requests\Department\Update;
use App\Http\Resources\DepartmentResource;
use App\Http\Services\DepartmentService;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    private $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Index $request)
    {
        return $this->departmentService->getAll(filters: $request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->departmentService->create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $departmentId)
    {
        return $this->departmentService->findById($departmentId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id, Update $request)
    {
        return $this->departmentService->updateById($id, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->departmentService->deleteById($id);
    }
}
