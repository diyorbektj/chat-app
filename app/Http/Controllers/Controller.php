<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success(mixed $data, string $message="ok",int $statusCode=200): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                'data' => $data,
                'success' => true,
                'message' => $message
            ], $statusCode
        );
    }

    public function error(string $message, int $statusCode=422):\Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                'data' => null,
                'success' => false,
                'message'=> $message,
            ], $statusCode
        );
    }
}
