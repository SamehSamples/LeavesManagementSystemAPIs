<?php

namespace App\Repository\Interfaces;

use App\Models\Leave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface LeaveRepositoryInterface
{
    public function create(array $attributes):Leave;
    public function update(array $attributes):Leave;
    public function index(bool $activeOnly): Collection;
    public function getByID(int $leaveID, bool $activeOnly):?Leave;
    public function makeActive(array $attributes);
    public function makeInactive(array $attributes);
}
