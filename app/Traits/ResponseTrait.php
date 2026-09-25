<?php

namespace App\Traits;

trait ResponseTrait
{
    public function successResponse($data, $message = 'نجاح', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $status);
    }

    public function errorResponse($message = 'خطأ', $status = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
