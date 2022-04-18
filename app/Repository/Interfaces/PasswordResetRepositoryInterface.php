<?php

namespace App\Repository\Interfaces;

interface PasswordResetRepositoryInterface
{
    public function requestPasswordReset(array $attributes);
    public function findPasswordResetToken(array $attributes):array;
    public function resetPassword(array $attributes);
}
