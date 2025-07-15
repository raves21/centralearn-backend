<?php

namespace App\Http\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{

    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findById(string $id, array $relationships = [])
    {
        return $this->model->with($relationships)->findOrFail($id);
    }

    public function updateById(string $id, array $formData)
    {
        $record = $this->model->findOrFail($id);
        $record->update($formData);
        return $record->fresh();
    }

    public function getAll(array $relationships = [], array $filters = [], string $orderBy = 'created_at', string $sortDirection = 'desc')
    {
        return $this->model->with($relationships)
            ->when(!empty($filters), function ($query) use ($filters) {
                foreach ($filters as $key => $value) {
                    $query->where($key, $value);
                }
            })
            ->orderBy($orderBy, $sortDirection)
            ->paginate();
    }
}
