<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function apiResponse($status, $data = null, $message = null, $code = 200)
    {
        return response()->json([
            'status' => $status,
            'data'   => $data,
            'message' => $message,
        ], $code);
    }

    public function sendSuccess($data, $message = "Success")
    {
        return $this->apiResponse(true, $data, $message, 200);
    }

    public function sendError($message, $code = 400)
    {
        return $this->apiResponse(false, null, $message, $code);
    }
}
