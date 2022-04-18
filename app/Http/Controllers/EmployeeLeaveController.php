<?php

namespace App\Http\Controllers;

use App\Repository\Eloquent\EmployeeRepository;
use App\Repository\Interfaces\EmployeeLeaveRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EmployeeLeaveController extends Controller
{
    private EmployeeLeaveRepositoryInterface $repository;

    public function __construct(EmployeeLeaveRepositoryInterface $employeeLeaveRepository)
    {
        $this->repository = $employeeLeaveRepository;
    }

    public function getLeaveBalance(Request $request):JsonResponse
    {
        try{
            return response()->json(['balance' => $this->repository->checkLeaveBalance($request->all())], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json([
                'message' => $ex->getMessage()
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function applyForLeave(Request $request):JsonResponse
    {
        try{
            //request leave
            $this->repository->requestLeave($request->all());
            //return success response
            return response()->json(['message' => 'leave application request successfully submitted'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function withdrawLeave(Request $request):JsonResponse
    {
        try{
            //withdraw Leave
            $this->repository->withdrawLeave($request->all());
            //return success response
            return response()->json(['message' => 'leave application successfully withdrawn'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getManagerLeaveRequests(Request $request):JsonResponse
    {
        try{
            $leaves = $this->repository->getManagerLeaveRequests($request->all());
            //return success response
            return response()->json(['data' => $leaves], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function actionLeave(Request $request, EmployeeRepository $employeeRepo):JsonResponse
    {
        try{
            //apply action to an employee leave
            $this->repository->actionLeave($request->all());
            //return success response
            return response()->json(['message' => 'leave application successfully actioned'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
