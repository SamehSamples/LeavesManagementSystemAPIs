<?php

namespace App\Http\Controllers;

use App\Repository\Interfaces\DepartmentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DepartmentController extends Controller
{
    private DepartmentRepositoryInterface $repository;

    public function __construct(DepartmentRepositoryInterface $departmentRepository){
        $this->repository=$departmentRepository;
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

    public function assignManager(Request $request):JsonResponse
    {
        try{
            //Assign Manager
            $this->repository->assignManager($request->all());

            //return success response
            return response()->json(['message' => 'manager successfully assigned'], ResponseAlias::HTTP_CREATED);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
