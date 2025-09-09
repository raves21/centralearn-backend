<?php

namespace App\Http\Services;

use App\Http\Repositories\AdminRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\AdminResource;
use App\Models\Role;

class AdminService
{
    private $adminRepo;
    private $userRepo;

    public function __construct(AdminRepository $adminRepo, UserRepository $userRepo)
    {
        $this->adminRepo = $adminRepo;
        $this->userRepo = $userRepo;
    }

    public function getAll(array $filters)
    {
        return AdminResource::collection($this->adminRepo->getAll($filters));
    }

    public function findById(string $id)
    {
        return new AdminResource($this->adminRepo->findById($id));
    }

    public function create(array $formData)
    {
        $newUser = $this->userRepo->create($formData);
        $newUser->assignRole(Role::ADMIN);
        return new AdminResource($this->adminRepo->create([...$formData, 'user_id' => $newUser->id]));
    }

    public function updateById(string $id, array $formData)
    {
        $user = $this->adminRepo->findById($id)->user;
        $this->userRepo->updateById($user->id, $formData);
        return new AdminResource($this->adminRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->adminRepo->deleteById($id);
    }
}
