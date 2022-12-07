<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($result, $message, $code = 200){

        $response = [
            'response_code' => $code,
            'success' => true,
            'message' => $message,
            'data' => $result,
        ];
        return response()->json($response,$code);
    }
    public function sendError($errorMessages, $result =[], $code=404){
        
        $response = [

            'responseCode'  =>  $code,
            'success' => false,
            'message' => $errorMessages,
            'errors'   =>  $result
        ];

        return response()->json($response, $code);
    }
    public function sendUserExists($errorMessages,$result = [], $code=401){
        $response = [

            'responseCode'  =>  $code,
            'success' => false,
            'message' => $errorMessages,
            'errors'   =>  $result
        ];
    }
}
