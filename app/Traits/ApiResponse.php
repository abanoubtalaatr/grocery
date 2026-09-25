<?php 
namespace App\Traits;

trait ApiResponse
{
    public function success($message = "", $code = 200, $data = null)
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'code'    => $code
        ], $code);
    }

    public function error($message = "", $code = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'data'    => $data,
            'message' => $message
        ], $code);
    }
}