<?php

namespace App\Validators;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class InputsValidator
{
    public function validateUserInputs($inputs, $validationCriteria){
        //$validationCriteria=Arr::Only($allValidationCriteria,$validationFields);

        $validator = Validator::make($inputs,$validationCriteria);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}
