<?php

namespace App\Repository\Interfaces;

use App\Models\Employee;
use Illuminate\Support\Collection;

interface EmployeeRepositoryInterface
{
    public function index(bool $activeOnly= true): Collection;
    public function getByID(int $id, bool $activeOnly = true):?Employee;
    public function create(array $inputs):Employee;
    public function update(array $inputs):Employee;
    public function moveToDepartment(array $inputs);
    public function incrementSalary(array $inputs);
    public function changeJobTitle(array $inputs);
    public function terminateServices(array $inputs);
    public function disableSelfServices(int $id);
    public function enableSelfServices(int $id);
    public function getEmployeeLeavesList(array $attributes);
    public function getEmployeeTransactionLogs(array $attributes);
}
