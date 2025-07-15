<?php

namespace App\Http\Services;

use App\Http\Repositories\AdminRepository;
use App\Http\Resources\AdminResource;
use App\Http\Resources\UserResource;

class AdminService
{

    protected $adminRepo;

    public function __construct(AdminRepository $adminRepo)
    {
        $this->adminRepo = $adminRepo;
    }

    public function getAll()
    {
        return AdminResource::collection($this->adminRepo->getAll());
    }

    public function findById(string $id)
    {
        return new AdminResource($this->adminRepo->findById($id));
    }
}
