<?php

namespace App\Repository\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function all(): Collection;
    public function getByID(int $userID, bool $activeOnlyUser = true):?Model;
    public function getByEmail(string $email):?Model;
    public function create(array $inputs):Model;
    public function logUser(array $inputs):array;
    public function update(array $inputs):Model;
    public function changePassword(array $inputs):string;
    public function changeStatus(array $inputs):Model;
    public function verifyEmail(int $id);
    public function resendVerificationEmail(array $inputs);
}
