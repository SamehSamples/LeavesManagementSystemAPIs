<?php

namespace App\Repository\Eloquent;

use App\Validators\InputsValidator;
use Aws\Credentials\CredentialProvider;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImageRepository extends BaseRepository implements \App\Repository\Interfaces\ImageRepositoryInterface
{
    private InputsValidator $inputsValidator;

    public function __construct(InputsValidator $inputsValidator)
    {
        $this->inputsValidator = $inputsValidator;
    }

    public function uploadImage(Request $request)
    {
        $attributes=$request->all();
        $validationFields = [
            'avatar',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        //identify file type and construct file name
        $file_mimeType = $request->file('avatar')->getMimeType();
        $file_ext = substr($file_mimeType, strpos($file_mimeType, "/") + 1);
        $file_name= Auth::user()->id . '_' . Carbon::now()->format('YmdHs') . '.' . $file_ext;

        $result= self::UploadFile($request->file('avatar'),
            $file_name,$file_mimeType,
            'appOperationalContent/',
            env('AWS_BUCKET'),
            env('AWS_DEFAULT_REGION'),
            's3'
        );

        return $result;
    }

    public function deleteImage(array $attributes)
    {
        $validationFields = [
            'file_name',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $result=self::DeleteFile($attributes['file_name'],
            'appOperationalContent/',
            env('AWS_BUCKET'),
            env('AWS_DEFAULT_REGION')
        );

        return $result;

    }

    public function UploadFile($file,$file_name,$file_mimeType,$key,$bucket,$region,$disk, $file_Type = 'path'):array{
        try {
            //Create S3 Client to access buckets
            $provider = CredentialProvider::defaultProvider();
            $s3 = new S3Client([
                'credentials' => $provider,
                'region'  => $region,
                'version' => 'latest'
            ]);

            $objectInfoArray = [
                'Bucket'     => $bucket,
                'Key'        => $key . $file_name,
                'ACL'    => 'public-read',
                'ContentType'=>$file_mimeType,
            ];

            if($file_Type==='stream'){
                $objectInfoArray['Body'] = $file;
            }else{
                $objectInfoArray['SourceFile'] = $file;
            }

            //Put Image into AWS S3 Folder [appOperationalContent]
            $s3->putObject($objectInfoArray);

            return [
                'status'=>true,
                'message'=>'File successfully stored',
                'file_name'=>basename($file_name),
                'url'=>Storage::disk($disk)->url($key . $file_name)
            ];
        }catch(S3Exception $ex){
            //S3 error handler
            return ['status'=>false
                ,'message' => $ex->getMessage()
                , 'error_code' => $ex->getCode()
                , 'error_file' => $ex->getFile()
                , 'error_line' => $ex->getLine()];
        }
    }
    public function DeleteFile($file_name,$key,$bucket,$region):array{
        try{
            //Create S3 Client to access buckets
            $provider = CredentialProvider::defaultProvider();
            $s3 = new S3Client([
                'credentials' => $provider,
                'region'  => $region,
                'version' => 'latest'
            ]);

            //Delete Image
            $s3->deleteObject([
                'Bucket'     => $bucket,
                'Key' => $key.$file_name, // REQUIRED
            ]);
            return ['status'=>true];
        }catch(S3Exception $ex){
            //S3 error handler
            return ['status'=>false
                ,'message' => $ex->getMessage()
                , 'error_code' => $ex->getCode()
                , 'error_file' => $ex->getFile()
                , 'error_line' => $ex->getLine()];
        }
    }

    private array $allModelValidationCriteria= [
        'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'file_name' => 'required|string',
    ];

}
