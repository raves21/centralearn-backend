<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProgramResource;
use App\Http\Services\ProgramService;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    protected $programService;

    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->programService->getAll();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, Program $program)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        //
    }
}
