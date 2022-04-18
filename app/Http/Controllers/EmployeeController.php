<?php

namespace App\Http\Controllers;

use App\Repository\Interfaces\EmployeeRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EmployeeController extends Controller
{
    private EmployeeRepositoryInterface $repository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->repository = $employeeRepository;
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

    public function moveToDepartment(Request $request):JsonResponse
    {
        try{
            //move to new department
            $this->repository->moveToDepartment($request->all());

            //return success response
            return response()->json(['message' => 'employee moved to new department'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function incrementSalary(Request $request):JsonResponse
    {
        try{
            //Increase employee salary
            $this->repository->incrementSalary($request->all());

            //return success response
            return response()->json(['message' => 'employee salary successfully incremented'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function changeJobTitle(Request $request):JsonResponse
    {
        try{
            //change employee's job title
            $this->repository->changeJobTitle($request->all());

            //return success response
            return response()->json(['message' => 'employee job title successfully changed'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function terminateEmployeeServices(Request $request):JsonResponse
    {
        try{
            //terminate employee service
            $this->repository->terminateServices($request->all());

            //return success response
            return response()->json(['message' => 'employee services terminated successfully'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function disableSelfServices(Request $request):JsonResponse
    {
        try{
            // disable user Self Services
            $this->repository->disableSelfServices($request->id);

            //return success response
            return response()->json(['message' => 'employee self-services successfully disabled'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function enableSelfServices(Request $request):JsonResponse
    {
        try{
            // disable user Self Services
            $this->repository->enableSelfServices($request->id);

            //return success response
            return response()->json(['message' => 'employee self-services successfully enabled'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getEmployeeLeavesList(Request $request):JsonResponse
    {
        try{
            //return success response
            return response()->json(['data' => $this->repository->getEmployeeLeavesList($request->all())], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getEmployeeTransactionLogs(Request $request):JsonResponse
    {
        try{
            //return success response
            return response()->json(['data' => $this->repository->getEmployeeTransactionLogs($request->all())], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json([
                'message' => $ex->getMessage(),
                'file'=>$ex->getFile(),
                'line'=>$ex->getLine()
                ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
