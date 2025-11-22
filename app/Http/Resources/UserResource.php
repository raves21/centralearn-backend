<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'address' => $this->address,
            'roles' => $this->getRoleNames(),
            $this->mergeWhen($this->hasRole('admin'), fn() => ['adminId' => $this->admin->id]),
            $this->mergeWhen($this->hasRole('student'), fn() => ['studentId' => $this->student->id]),
            $this->mergeWhen($this->hasRole('instructor'), fn() => ['instructorId' => $this->instructor->id]),
            $this->mergeWhen(
                $this->additional && $this->additional['with_permissions'],
                fn() => ['permissions' => $this->getAllPermissions()->pluck('name')]
            )
        ];
    }
}
