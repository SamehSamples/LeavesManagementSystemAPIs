<?php

namespace App\Http\Helpers;

use Illuminate\Support\Arr;

class GeneralHelper
{
    public function checkArrayKeyExists(array $inputs, string $key):bool
    {
        return Arr::exists($inputs,$key) &&
            !is_null($inputs[$key]) &&
            str_replace(' ', '',$inputs[$key]) != '';
    }
}
