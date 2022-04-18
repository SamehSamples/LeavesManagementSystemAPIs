<?php

namespace App\Repository\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface EloquentRepositoryInterface
{
    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes);

    /**
     * @param $id
     * @return Model
     */
    public function find($id);

    /**
     * @param
     * @return Model
     */
    public function first();

}
