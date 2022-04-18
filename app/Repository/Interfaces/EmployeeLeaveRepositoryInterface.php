<?php

namespace App\Repository\Interfaces;

use App\Models\Employee;
use App\Models\Leave;

interface EmployeeLeaveRepositoryInterface
{
    public function checkLeaveBalance(array $inputs):array;
    public function getManagerLeaveRequests(array $attributes);
    public function requestLeave (array $attributes);
    public function checkLeaveEligibility(Employee $employee, Leave $leave, int $duration):bool;
    public function withdrawLeave (array $attributes):bool;
    public function actionLeave (array $attributes):bool;
    public function updateLeaveStatus();
}
