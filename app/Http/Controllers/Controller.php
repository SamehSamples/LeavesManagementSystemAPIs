<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function all($repo, bool $onlyActive=true):JsonResponse
    {
        try{
            return response()->json([
                'data' => $repo->index($onlyActive)
            ], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function get($repo, int $id, bool $onlyActive=true):JsonResponse
    {
        try{
            return response()->json([
                'data' => $repo->getByID($id,$onlyActive)
            ], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function create($repo, Request $request):JsonResponse
    {
        try{
            //return success response with department object
            return response()->json(['data' => $repo->create($request->all())], ResponseAlias::HTTP_CREATED);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function modify($repo,Request $request):JsonResponse
    {
        try{
            //return success response
            return response()->json(['data' => $repo->update($request->all())], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function makeActive($repo,Request $request):JsonResponse
    {
        try{
            //Activate Model
            $repo->makeActive($request->all());

            //return success response
            return response()->json(['message' => 'successfully activated'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function makeInactive($repo, Request $request):JsonResponse
    {
        try{
            //Deactivate Model
            $repo->makeInactive($request->all());

            //return success response
            return response()->json(['message' => 'successfully deactivated'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
