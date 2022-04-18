<?php

namespace App\Repository\Interfaces;

use App\Models\Department;
use Illuminate\Support\Collection;

interface DepartmentRepositoryInterface
{
    public function index(bool $activeOnly): Collection;
    public function getByID(int $id, bool $activeOnly = true):?Department;
    public function create(array $inputs):Department;
    public function update(array $inputs):Department;
    public function assignManager(array $inputs);
    public function makeActive(array $attributes);
    public function makeInactive(array $attributes);
}
