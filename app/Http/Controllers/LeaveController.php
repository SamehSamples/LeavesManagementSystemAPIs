<?php

namespace App\Http\Controllers;

use App\Repository\Interfaces\LeaveRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    private LeaveRepositoryInterface $repository;

    public function __construct(LeaveRepositoryInterface $repository){
        $this->repository=$repository;
    }

    public function index(bool $onlyActive=true):JsonResponse
    {
        return parent::all($this->repository,$onlyActive);
    }

    public function show(int $id, $onlyActive=true):JsonResponse
    {
        return parent::get($this->repository,$id,$onlyActive);
    }

    public function store(Request $request):JsonResponse
    {
        return parent::create($this->repository, $request);
    }

    public function update(Request $request):JsonResponse
    {
        return parent::modify($this->repository, $request);
    }

    public function activate(Request $request):JsonResponse
    {
        return parent::makeActive($this->repository,$request);
    }

    public function deactivate(Request $request):JsonResponse
    {
        return parent::makeInactive($this->repository,$request);
    }
}
