<?php

namespace App\Repository\Eloquent;

use Illuminate\Database\Eloquent\Model;

class BaseRepository implements \App\Repository\Interfaces\EloquentRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * @inheritDoc
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * @inheritDoc
     */
    public function first()
    {
        return $this->model->first();
    }

    public function select(array $columns)
    {
        $this->model = $this->model->select($columns);
        return $this;
    }

    public function where(array $columns)
    {
        $this->model = $this->model->where($columns);
        return $this;
    }
}
