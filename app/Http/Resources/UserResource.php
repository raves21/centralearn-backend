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

            $this->mergeWhen(
                $this->additional && $this->additional['with_permissions'],
                fn() => ['permissions' => $this->getAllPermissions()->pluck('name')]
            )
        ];
    }
}
