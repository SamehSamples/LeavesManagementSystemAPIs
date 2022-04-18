<?php

namespace App\Http\Controllers;

use App\Repository\Interfaces\ImageRepositoryInterface;
use http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ImageController extends Controller
{
    private ImageRepositoryInterface $repository;

    public function __construct(ImageRepositoryInterface $imageRepository)
    {
        $this->repository=$imageRepository;
    }

    public function uploadImage(Request $request):JsonResponse
    {
        try{
            $result=$this->repository->uploadImage($request);
            return response()->json(['result'=>$result],$result['status']?ResponseAlias::HTTP_OK:ResponseAlias::HTTP_BAD_REQUEST);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteImage(Request $request):JsonResponse
    {
        try{
            $result=$this->repository->deleteImage($request->all());

            //Return Failure Response
            if(!$result['status']) {
                return response()->json([
                    'result' => $result
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            //Return Success Response
            return response()->json(
                ['message' => 'Image Deleted',
                    'file_name'=>$request->file_name], ResponseAlias::HTTP_OK);
            //return response()->json(['result'=>$result],$result['status']?ResponseAlias::HTTP_OK:ResponseAlias::HTTP_BAD_REQUEST);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
