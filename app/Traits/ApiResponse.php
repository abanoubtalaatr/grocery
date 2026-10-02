<?php 
namespace App\Traits;

trait ApiResponse
{
    public function success($message = "", $code = 200,$data){
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'code'=> $code
        ]);
    }

    public function error($message = "", $code = 400,$data){
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
    }
}
