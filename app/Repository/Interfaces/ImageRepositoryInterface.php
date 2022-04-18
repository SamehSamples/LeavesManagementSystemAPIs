<?php

namespace App\Repository\Interfaces;

use Illuminate\Http\Request;

interface ImageRepositoryInterface
{
    public function uploadImage(Request $request);
    public function deleteImage(array $attributes);
}
