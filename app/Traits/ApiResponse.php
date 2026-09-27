<?php 
namespace App\Traits;

trait ApiResponse
{
    public function success($data,$message = "" ,$code = 200){
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
