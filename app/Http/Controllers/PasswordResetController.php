<?php

namespace App\Http\Controllers;

use App\Repository\Interfaces\PasswordResetRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PasswordResetController extends Controller
{
    private PasswordResetRepositoryInterface $repository;

    public function __construct(PasswordResetRepositoryInterface $passwordResetRepository){
        $this->repository= $passwordResetRepository;
    }

    public function requestPasswordReset(Request $request):JsonResponse
    {
        try {
            //Create password reset request
            $this->repository->requestPasswordReset($request->all());

            //return success response
            return response()->json(['message' => 'password reset link emailed'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //general error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function findPasswordResetToken(Request $request):JsonResponse
    {
        try {
            //Create password reset request
            $responseInfo = $this->repository->findPasswordResetToken($request->all());

            //return success response
            return response()->json($responseInfo, ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //general error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function resetPassword(Request $request):JsonResponse
    {
        try {
            //Create password reset request
            $info = $this->repository->resetPassword($request->all());

            //return success response
            return response()->json(['message'=>'Password changed successfully'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //general error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
