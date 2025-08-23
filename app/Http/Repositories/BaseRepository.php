<?php

namespace App\Http\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BaseRepository
{

    private Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findById(string $id, array $relationships = [])
    {
        return $this->model->with($relationships)->findOrFail($id);
    }

    public function findByFilter(array $filters)
    {
        return get_class($this->model)::where(function ($q) use ($filters) {
            foreach ($filters as $key => $value) {
                $q->where($key, $value);
            }
        })
            ->first();
    }

    public function ensureExists(string $id)
    {
        $this->model->findOrFail($id);
    }

    public function updateById(string $id, array $formData, array $relationships = [])
    {
        $record = $this->model->findOrFail($id);
        $record->update($formData);
        return $record->load($relationships);
    }

    public function create(array $formData, array $relationships = [])
    {
        $record = $this->model->create($formData);
        $record->load($relationships);
        return $record;
    }

    public function deleteById(string $id)
    {
        $record = $this->model->findOrFail($id);
        $record->delete();
        return true;
    }

    public function deleteByFilter(array $filters)
    {
        $query = $this->model->query();
        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }
        $model = $query->first();

        if ($model) {
            return $model->delete();
        }

        return false;
    }

    public function deleteManyById(array $ids)
    {
        $this->model->destroy($ids);
        return true;
    }

    public function getAll(
        array $relationships = [],
        array $filters = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        $query = $this->model->with($relationships)
            ->when(!empty($filters), function ($query) use ($filters) {
                foreach ($filters as $column => $value) {
                    if (Schema::hasColumn($this->model->getTable(), $column)) {
                        $query->where($column, $value);
                    }
                }
            })
            ->orderBy($orderBy, $sortDirection);

        if ($paginate) return $query->paginate();
        return $query->get();
    }

    public function getFresh(Model $record)
    {
        return $record->fresh();
    }

    public function loadRelationships(Model $record, array $relationships = [])
    {
        return $record->load($relationships);
    }

    public function deleteMorph($morphType, $morphId)
    {
        $morphType::destroy($morphId);
    }
}
