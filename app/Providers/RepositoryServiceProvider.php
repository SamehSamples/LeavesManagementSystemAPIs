<?php

namespace App\Providers;

use App\Repository\Eloquent\BaseRepository;
use App\Repository\Eloquent\DepartmentRepository;
use App\Repository\Eloquent\EmployeeLeaveRepository;
use App\Repository\Eloquent\EmployeeRepository;
use App\Repository\Eloquent\ImageRepository;
use App\Repository\Eloquent\LeaveRepository;
use App\Repository\Eloquent\PasswordResetRepository;
use App\Repository\Eloquent\UserRepository;
use App\Repository\Interfaces\DepartmentRepositoryInterface;
use App\Repository\Interfaces\EloquentRepositoryInterface;
use App\Repository\Interfaces\EmployeeLeaveRepositoryInterface;
use App\Repository\Interfaces\EmployeeRepositoryInterface;
use App\Repository\Interfaces\ImageRepositoryInterface;
use App\Repository\Interfaces\LeaveRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Repository\Interfaces\PasswordResetRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(LeaveRepositoryInterface::class, LeaveRepository::class);
        $this->app->bind(EmployeeLeaveRepositoryInterface::class, EmployeeLeaveRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(PasswordResetRepositoryInterface::class, PasswordResetRepository::class);
        $this->app->bind(ImageRepositoryInterface::class, ImageRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
