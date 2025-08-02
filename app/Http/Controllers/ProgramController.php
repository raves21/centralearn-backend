<?php

namespace App\Http\Controllers;

use App\Http\Requests\Program\Index;
use App\Http\Requests\Program\Store;
use App\Http\Requests\Program\Update;
use App\Http\Resources\ProgramResource;
use App\Http\Services\ProgramService;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    private $programService;

    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Index $request)
    {
        return $this->programService->getAll(filters: $request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store $request)
    {
        return $this->programService->create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $programId)
    {
        return $this->programService->findById($programId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id, Update $request)
    {
        return $this->programService->updateById($id, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //can only delete program if this program has no students
        return $this->programService->deleteById($id);
    }
}
